#!/usr/bin/env python3
"""
build_blog_tr.py — Translate EN blog article to TR, preserving all HTML/CSS.
Two-step workflow:
  1. python3 build_blog_tr.py extract      → saves translation blocks to data/{lang}/blog/{slug}-tr.json
  2. Agent translates the blocks
  3. python3 build_blog_tr.py build --slug={slug}  → applies translations, renders via template

No blogtr.txt dependency. All HTML structure preserved.
"""

import sys, os, re, json
from pathlib import Path

BASE = Path(__file__).resolve().parent.parent
LANG = 'tr'
DATA_DIR = BASE / 'data' / LANG / 'blog'
BLOG_DIR = BASE / LANG / 'blog'
EN_BLOG_DIR = BASE / 'en' / 'blog'
TEMPLATES = BASE / 'templates'

TITLE_MAP = {
    'Almanya': 'germany-hidden-costs.html',
    'İlk Kez': 'first-time-exhibitor-guide.html',
    'Dubai': 'dubai-hidden-costs.html',
    'Moskova': 'moskova-hidden-costs.html',
}

SLUG_MAP = {
    'germany-hidden-costs.html': 'almanya-hidden-costs.html',
    'first-time-exhibitor-guide.html': 'ilk-kez-katilacaklar-rehberi.html',
    'dubai-hidden-costs.html': 'dubai-hidden-costs.html',
    'moskova-hidden-costs.html': 'moskova-hidden-costs.html',
}


def find_en_file(keyword: str) -> str | None:
    for k, f in TITLE_MAP.items():
        if k.lower() in keyword.lower():
            return f
    return None


def extract_blocks(en_path: Path) -> dict:
    """Extract all translatable text blocks from article-intro."""
    html = en_path.read_text(encoding='utf-8', errors='ignore')
    
    # Extract article-intro content
    m = re.search(r'<div class="article-intro">(.*?)<div class="author-box">', html, re.DOTALL)
    if not m:
        raise ValueError(f"article-intro not found in {en_path}")
    
    article = m.group(1)
    title = re.search(r'<h1>([^<]+)</h1>', html)
    lead = re.search(r'<p class="lead">([^<]+)</p>', html)
    meta_desc = re.search(r'<meta name="description" content="([^"]+)"', html)
    
    # Extract all text-bearing elements with their exact HTML
    blocks = []
    pattern = re.compile(
        r'<(p|h2|h3|h4|li|blockquote|summary|figcaption)(\s[^>]*)?>(.*?)</\1>',
        re.DOTALL
    )
    
    for m in pattern.finditer(article):
        tag = m.group(1)
        attrs = m.group(2) or ''
        inner = m.group(3)
        full = m.group(0)
        
        # Extract clean text for translation
        clean = re.sub(r'<[^>]+>', '', inner).strip()
        if not clean:
            continue
        
        # Detect if English
        en_words = ['the', 'and', 'you', 'your', 'that', 'this', 'with', 'from', 'they']
        words = clean.lower().split()
        en_count = sum(1 for w in words if w in en_words)
        is_en = en_count >= 2 and len(words) > 3
        
        # Also translate headings and structural elements regardless
        if tag in ('h2', 'h3', 'h4', 'summary'):
            is_en = True
        
        blocks.append({
            'id': len(blocks),
            'tag': tag,
            'attrs': attrs,
            'inner': inner,
            'clean': clean,
            'translated': None,
            'needs_translation': is_en,
        })
    
    return {
        'en_file': en_path.name,
        'tr_slug': SLUG_MAP.get(en_path.name, en_path.name),
        'title': title.group(1) if title else '',
        'lead': lead.group(1) if lead else '',
        'meta_desc': meta_desc.group(1) if meta_desc else '',
        'blocks': blocks,
        'stats': {
            'total': len(blocks),
            'needs_translation': sum(1 for b in blocks if b['needs_translation']),
        }
    }


def apply_translations(data: dict) -> str:
    """Build translated article by iterating EN elements and applying translations."""
    en_path = EN_BLOG_DIR / data['en_file']
    html = en_path.read_text(encoding='utf-8', errors='ignore')
    m = re.search(r'<div class="article-intro">(.*?)<div class="author-box">', html, re.DOTALL)
    article = m.group(1)
    
    # Build a lookup: block_id → translated text
    tr_lookup = {}
    for block in data['blocks']:
        if block.get('translated') and block['translated'] != block['clean']:
            tr_lookup[block['id']] = block['translated']
    
    # Rebuild article: iterate through text elements, replace each with translation
    result = []
    pos = 0
    block_idx = 0
    
    tag_pattern = re.compile(
        r'<(p|h2|h3|h4|li|blockquote|summary|figcaption)(\s[^>]*)?>(.*?)</\1>',
        re.DOTALL
    )
    
    for m in tag_pattern.finditer(article):
        tag = m.group(1)
        attrs = m.group(2) or ''
        inner = m.group(3)
        
        # Add text between matches
        result.append(article[pos:m.start()])
        pos = m.end()
        
        # Apply translation if available for this block index
        if block_idx < len(data['blocks']) and block_idx in tr_lookup:
            tr_text = tr_lookup[block_idx]
            # Preserve nested tags: replace text-only content
            clean_en = re.sub(r'<[^>]+>', '', inner).strip()
            if clean_en:
                # Try replacing clean text first
                new_inner = inner.replace(clean_en, tr_text)
                # If that didn't work (nested tags), just use TR text with original tags
                if new_inner == inner and '<' in inner:
                    # Has nested tags, wrap TR text with first tag structure
                    nested = re.findall(r'(<[^>]+>)', inner)
                    if nested:
                        new_inner = nested[0] + tr_text + nested[-1] if len(nested) > 1 else tr_text
                    else:
                        new_inner = tr_text
                result.append(f'<{tag}{attrs}>{new_inner}</{tag}>')
            else:
                result.append(m.group(0))
        else:
            result.append(m.group(0))
        
        block_idx += 1
    
    result.append(article[pos:])
    return ''.join(result)


def render_page(slug: str, content: str, title: str, lead: str) -> str:
    """Render the blog page via blog-template.php and return HTML."""
    import subprocess
    
    # Update JSON — preserve existing fields, only update content
    slug_clean = slug.replace('.html', '')
    jp = DATA_DIR / f'{slug_clean}.json'
    if jp.exists():
        data = json.loads(jp.read_text())
    else:
        data = {}
    
    # Only update these fields
    data['title'] = title
    data['summary'] = lead
    data['meta_desc'] = lead[:160] if lead else ''
    data['content'] = content
    
    jp.write_text(json.dumps(data, ensure_ascii=False, indent=4))
    
    # Render via PHP template (write to temp file to avoid shell escaping issues)
    slug_clean = slug.replace('.html', '')
    php_code = f'''<?php
$b = "{BASE}";
require "$b/admin/config.php";
require "$b/templates/blog-template.php";
$d = json_decode(file_get_contents("{jp}"), true);
$content = $d["content"] ?? " ";
$h = render_blog($d, $content, "tr");
file_put_contents("{BLOG_DIR}/{slug_clean}.html.tmp", $h);
rename("{BLOG_DIR}/{slug_clean}.html.tmp", "{BLOG_DIR}/{slug_clean}.html");
echo "OK:" . strlen($h);
'''
    tmp = BASE / 'tmp_render.php'
    tmp.write_text(php_code)
    r = subprocess.run(['php', str(tmp)], capture_output=True, text=True, cwd=str(BASE))
    tmp.unlink(missing_ok=True)
    if r.returncode != 0:
        raise RuntimeError(f"PHP render failed: {r.stderr}")
    return r.stdout.strip()


# ── CLI ──

def cmd_extract():
    """Extract translation blocks from the first matching blog."""
    # Auto-detect EN blog from blogtr.txt or use all
    en_files = sorted(EN_BLOG_DIR.glob('*.html'))
    for en_path in en_files:
        data = extract_blocks(en_path)
        tr_path = DATA_DIR / f"{data['tr_slug']}-tr.json"
        tr_path.write_text(json.dumps(data, ensure_ascii=False, indent=2))
        print(f"[{data['en_file']}] → {tr_path.name}")
        print(f"  Blocks: {data['stats']['total']} total, {data['stats']['needs_translation']} need translation")
        print(f"  Title: {data['title'][:80]}")


def cmd_build(slug: str):
    """Apply translations and render the page."""
    tr_path = DATA_DIR / f'{slug}-tr.json'
    if not tr_path.exists():
        print(f"ERROR: {tr_path} not found. Run 'extract' first.")
        sys.exit(1)
    
    data = json.loads(tr_path.read_text())
    
    # Check if all blocks are translated
    missing = sum(1 for b in data['blocks'] if b['needs_translation'] and not b['translated'])
    if missing > 0:
        print(f"ERROR: {missing} blocks still need translation. Translate them in {tr_path} first.")
        print("  Set 'translated' field to Turkish text for each block with 'needs_translation': true")
        sys.exit(1)
    
    # Apply and render
    content = apply_translations(data)
    result = render_page(data['tr_slug'], content, data['title'], data['lead'])
    print(f"Rendered: {BLOG_DIR / data['tr_slug']}.html ({result})")
    print(f"Blocks: {data['stats']['total']} total, all translated")


def cmd_status():
    """Show translation status for all blog posts."""
    for tf in sorted(DATA_DIR.glob('*-tr.json')):
        data = json.loads(tf.read_text())
        total = data['stats']['total']
        need = data['stats']['needs_translation']
        done = sum(1 for b in data['blocks'] if b['translated'])
        pct = int(done / max(need, 1) * 100)
        bar = '█' * (pct // 10) + '░' * (10 - pct // 10)
        print(f"{bar} {data['tr_slug']:40s} {done}/{need} ({pct}%)")


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(__doc__)
        print("Commands: extract | build --slug=<name> | status")
        sys.exit(0)
    
    cmd = sys.argv[1]
    if cmd == 'extract':
        cmd_extract()
    elif cmd == 'build':
        slug = None
        for a in sys.argv[2:]:
            if a.startswith('--slug='):
                slug = a.split('=', 1)[1]
        if not slug:
            print("ERROR: --slug= required for build")
            sys.exit(1)
        cmd_build(slug)
    elif cmd == 'status':
        cmd_status()
    else:
        print(f"Unknown command: {cmd}")
        sys.exit(1)
