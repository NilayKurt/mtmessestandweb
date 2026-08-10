#!/usr/bin/env python3
"""
build_blog_tr.py — Build a translated blog page from blog{LANG}.txt on Desktop.

blog{LANG}.txt format:
  Line 1:  author/date line
  Line 2:  blank
  Line 3:  title (H1)
  Line 4:  subtitle/lead
  Line 5:  image alt text
  Line 6:  blank
  ---
  Content. Sections matching EN <h3> tags must be wrapped in **...**.
  **Heading order must match EN H3 order exactly.**
  Lines that are not EN <h3> equivalents stay as plain text.

Usage: python3 scripts/build_blog_tr.py [--lang tr]
"""

import sys, os, re, subprocess
from pathlib import Path

BASE = Path(__file__).resolve().parent.parent
DESKTOP = Path("/mnt/c/Users/nilay/OneDrive/Desktop")
LANG = sys.argv[2] if len(sys.argv) > 2 and sys.argv[1] == '--lang' else 'tr'

# ── 1. Read translation file ──
spec_file = DESKTOP / f"blog{LANG}.txt"
if not spec_file.exists():
    print(f"ERROR: {spec_file} not found on Desktop")
    sys.exit(1)

tr_md = spec_file.read_text(encoding='utf-8')

if '---' not in tr_md[:300]:
    lines = tr_md.splitlines()
    tr_md = '\n'.join(lines[:6] + ['---'] + lines[6:])

tr_lines = tr_md.splitlines()
tr_title = tr_lines[2].strip() if len(tr_lines) > 2 else ''

# ── 2. Match to EN blog post ──
TITLE_MAP = {
    'Almanya': 'germany-hidden-costs.html',
    'İlk Kez': 'first-time-exhibitor-guide.html',
    'Dubai': 'dubai-hidden-costs.html',
    'Moskova': 'moskova-hidden-costs.html',
}
best_en = None
for keyword, en_file in TITLE_MAP.items():
    if keyword.lower() in tr_title.lower():
        best_en = en_file
        break
if not best_en:
    print(f"ERROR: Could not match TR title to any EN post. Title: {tr_title}")
    sys.exit(1)

slug_map = {
    'germany-hidden-costs.html': 'almanya-hidden-costs.html',
    'first-time-exhibitor-guide.html': 'ilk-kez-katilacaklar-rehberi.html',
}
tr_slug = slug_map.get(best_en, best_en)
tr_path = BASE / LANG / 'blog' / tr_slug

# ── 3. Parse EN HTML ──
en_html = (BASE / 'en/blog' / best_en).read_text(encoding='utf-8', errors='ignore')
head_match = re.search(r'(<!DOCTYPE.*?)<body>', en_html, re.DOTALL)
head = head_match.group(1) if head_match else ""
body_match = re.search(r'<body>\s*(.*?)\s*</section>\s*</main>', en_html, re.DOTALL)
en_body = body_match.group(1) if body_match else ""
footer_match = re.search(r'<footer(.*?</html>)', en_html, re.DOTALL)
tail = footer_match.group(1) if footer_match else ""

# ── 4. Parse TR content into sections (only **...** headings) ──
tr_sections = {}
current = '_intro'
tr_sections[current] = []
content_started = False

for line in tr_lines:
    if not content_started:
        if line.strip() == '---':
            content_started = True
        continue
    s = line.strip()
    if s.startswith('**') and s.endswith('**') and len(s) > 3:
        current = s.strip('*')
        if current not in tr_sections:
            tr_sections[current] = []
    else:
        tr_sections[current].append(line)

tr_headings = [h for h in tr_sections if h != '_intro']

def tr_lines_to_html(lines):
    html = []
    for raw in lines:
        s = raw.strip()
        if not s:
            html.append('')
            continue
        if s.startswith('> '):
            html.append(f'      <blockquote class="mt-note"><p>{s[2:]}</p></blockquote>')
        elif s.startswith('* '):
            text = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', s[2:])
            html.append(f'      <p>• {text}</p>')
        elif s.startswith('**') and s.endswith('**'):
            html.append(f'      <h4>{s.strip("*")}</h4>')
        elif re.match(r'^\d+\.\s', s):
            text = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', s)
            html.append(f'      <p><strong>{text}</strong></p>')
        else:
            text = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', s)
            html.append(f'      <p>{text}</p>')
    return html

# ── 5. Build TR body (positional H3 matching) ──
en_lines = en_body.split('\n')
result_lines = []
replaced = set()
i = 0
heading_idx = 0

while i < len(en_lines):
    line = en_lines[i]
    h3_match = re.search(r'<h3>(.+?)</h3>', line)
    if h3_match:
        en_h3 = h3_match.group(1).strip()
        if heading_idx < len(tr_headings):
            tr_heading = tr_headings[heading_idx]
            heading_idx += 1
            if tr_sections.get(tr_heading):
                replaced.add(en_h3)
                result_lines.append(f'      <h3>{tr_heading}</h3>')
                result_lines.extend(tr_lines_to_html(tr_sections[tr_heading]))
                i += 1
                while i < len(en_lines):
                    if re.search(r'<(h[234]|blockquote)', en_lines[i]):
                        break
                    if re.search(r'</div>\s*$', en_lines[i]) and 'article' in en_lines[i]:
                        break
                    i += 1
                continue
    result_lines.append(line)
    i += 1

print(f"TR: {tr_title[:80]}")
print(f"EN: {best_en}")
print(f"Replaced {len(replaced)} sections, {heading_idx}/{len(tr_headings)} TR headings used")

not_replaced = set(re.findall(r'<h3>([^<]+)</h3>', en_body)) - replaced
if not_replaced:
    print(f"NOT replaced: {len(not_replaced)}")
    for r in sorted(not_replaced):
        print(f"  ✗ {r[:70]}")

# ── 6. Update meta (lang, title, SEO tags) ──
tr_sub = tr_lines[3].strip() if len(tr_lines) > 3 else ''
head = head.replace('<html lang="en">', f'<html lang="{LANG}">')
head = re.sub(r'<link rel="canonical"[^>]*>',
              f'<link rel="canonical" href="https://mtmessestand.com/{LANG}/blog/{tr_slug.replace(".html", "/")}">',
              head)
head = re.sub(r'<title>[^<]+</title>', f'<title>{tr_title} | MT Messe Stand</title>', head)

# OG / Twitter / description
head = re.sub(r'<meta name="description" content="[^"]*">',
              f'<meta name="description" content="{tr_sub}">', head)
head = re.sub(r'<meta property="og:title" content="[^"]*">',
              f'<meta property="og:title" content="{tr_title} | MT Messe Stand">', head)
head = re.sub(r'<meta property="og:description" content="[^"]*">',
              f'<meta property="og:description" content="{tr_sub}">', head)
head = re.sub(r'<meta property="og:url" content="[^"]*">',
              f'<meta property="og:url" content="https://mtmessestand.com/{LANG}/blog/{tr_slug.replace(".html", "/")}">', head)
head = re.sub(r'<meta name="twitter:title" content="[^"]*">',
              f'<meta name="twitter:title" content="{tr_title}">', head)
head = re.sub(r'<meta name="twitter:description" content="[^"]*">',
              f'<meta name="twitter:description" content="{tr_sub}">', head)

# ── 7. Assemble & write ──
full_html = head + '<body>\n' + '\n'.join(result_lines) + '\n</section>\n</main>\n<footer' + tail
tr_path.parent.mkdir(parents=True, exist_ok=True)
tr_path.write_text(full_html)
print(f"Written: {tr_path} ({len(full_html)} chars)")

# ── 8. Navbar & audit ──
navbar_js = BASE / 'assets/js/navbar.js'
njs = navbar_js.read_text()
if not (f"'{best_en}':" in njs and f"'{tr_slug}':" in njs):
    new_entries = f"    '{best_en}': {{ en: '{best_en}', tr: '{tr_slug}' }},\n    '{tr_slug}': {{ en: '{best_en}', tr: '{tr_slug}' }}"
    njs = njs.replace("  };\n\n  // Active page detection",
                       f"  {new_entries}\n  }};\n\n  // Active page detection")
    navbar_js.write_text(njs)
    print("navbar.js: updated ✓")

if (BASE / 'scripts/audit_links.py').exists():
    r = subprocess.run(['python3', str(BASE / 'scripts/audit_links.py')], cwd=BASE, capture_output=True, text=True)
    print("Audit: ✓ PASS" if r.returncode == 0 else f"Audit: ❌ FAIL\n{r.stdout[-300:]}")
