#!/usr/bin/env python3
"""
MT Messe Stand — Link Audit Script
Run before deploy to catch broken links, depth errors, and lang mismatches.
Usage: python3 scripts/audit_links.py [--check-external]
"""

import os, re, subprocess, sys
from pathlib import Path

BASE = Path(__file__).resolve().parent.parent  # project root
CHECK_EXTERNAL = '--check-external' in sys.argv

errors = 0
warnings = 0

def warn(msg):
    global warnings
    warnings += 1
    print(f"  ⚠  {msg}")

def err(msg):
    global errors
    errors += 1
    print(f"  ✗  {msg}")

def ok(msg):
    print(f"  ✓  {msg}")

# ---------------------------------------------------------------------------
# 1. INTERNAL LINK CHECK — all href/src must resolve
# ---------------------------------------------------------------------------
print("\n=== 1. INTERNAL LINKS ===")
html_files = sorted(BASE.rglob("*.html"))
html_files = [f for f in html_files if 'node_modules' not in str(f) and 'admin' not in str(f)]

seen = set()
for f in html_files:
    rel = str(f.relative_to(BASE))
    content = f.read_text(encoding='utf-8', errors='ignore')

    hrefs = re.findall(r'href="([^"]*)"', content)
    srcs = re.findall(r'src="([^"]*)"', content)

    for link, attr in [(l, 'href') for l in hrefs] + [(l, 'src') for l in srcs]:
        if link.startswith('#') or link.startswith('javascript') or \
           link.startswith('mailto:') or link.startswith('tel:') or \
           link.startswith('data:') or link.startswith('http'):
            continue

        # Strip fragment for filesystem check
        clean_link = link.split('#')[0]
        if not clean_link:
            continue  # pure fragment, skip

        target = (f.parent / clean_link).resolve()

        # Check if OUTSIDE project
        try:
            target.relative_to(BASE)
        except ValueError:
            # Absolute paths like /en/ are OK on a real server
            if link.startswith('/'):
                continue
            err(f"{rel}: {attr}=\"{link}\" → OUTSIDE project")
            continue

        if not target.exists():
            err(f"{rel}: {attr}=\"{link}\" → MISSING")

if errors == 0:
    ok("All internal links resolve")

# ---------------------------------------------------------------------------
# 2. NAVBAR LANGUAGE CONSISTENCY
# ---------------------------------------------------------------------------
print("\n=== 2. LANGUAGE CONSISTENCY ===")
navbar_js = BASE / "assets/js/navbar.js"
if navbar_js.exists():
    njs = navbar_js.read_text()
    # Extract lang codes from navbar.js
    navbar_langs = re.findall(r"code:\s*'([a-z]{2})'", njs)
    print(f"  Navbar.js languages: {navbar_langs}")

    # Check footer badges match
    for f in [BASE / "en/index.html", BASE / "tr/index.html"]:
        if not f.exists():
            continue
        content = f.read_text(encoding='utf-8', errors='ignore')
        # Find badge hrefs like /xx/
        badge_langs = set(re.findall(r'href="/([a-z]{2})/"', content))
        # Remove current page lang (href="#")
        extra = badge_langs - set(navbar_langs)
        missing = set(navbar_langs) - badge_langs - {f.parent.name}  # exclude self
        if extra:
            warn(f"{f.relative_to(BASE)}: extra footer badges: {extra}")
        if missing:
            warn(f"{f.relative_to(BASE)}: missing footer badges: {missing}")
        else:
            ok(f"{f.relative_to(BASE)} footer badges match navbar.js")

# ---------------------------------------------------------------------------
# 3. DEPTH CHECK — assets/ paths must match page depth
# ---------------------------------------------------------------------------
print("\n=== 3. ASSET DEPTH ===")
for f in html_files:
    rel = str(f.relative_to(BASE))
    depth = rel.count('/')

    # Expected asset prefix based on depth
    if depth == 0:
        expected = "assets/"
    elif depth == 1:
        expected = "../assets/"
    elif depth == 2:
        expected = "../../assets/"
    else:
        continue

    content = f.read_text(encoding='utf-8', errors='ignore')

    # Check all src/href pointing to assets/
    bad = []
    for pattern, attr in [(r'href="([^"]*assets/[^"]*)"', 'href'),
                           (r'src="([^"]*assets/[^"]*)"', 'src')]:
        for m in re.findall(pattern, content):
            if 'http' in m:
                continue
            if depth == 1 and m.startswith('../../assets/'):
                bad.append(m)
            elif depth == 2 and not m.startswith('../../assets/'):
                bad.append(m)
            elif depth == 0 and m.startswith('../'):
                bad.append(m)

    if bad:
        warn(f"{rel}: wrong asset depth → {bad[:3]}")

# ---------------------------------------------------------------------------
# 4. EXTERNAL LINKS
# ---------------------------------------------------------------------------
if CHECK_EXTERNAL:
    print("\n=== 4. EXTERNAL LINKS ===")
    seen_urls = set()
    for f in html_files:
        content = f.read_text(encoding='utf-8', errors='ignore')
        for url in re.findall(r'href="(https?://[^"]*)"', content):
            seen_urls.add(url)

    for url in sorted(seen_urls):
        try:
            r = subprocess.run(['curl', '-sI', '-o', '/dev/null', '-w', '%{http_code}',
                               '-L', '--max-time', '5', url],
                             capture_output=True, text=True, timeout=8)
            code = r.stdout.strip()
            if code == '200':
                continue
            if code in ('999', '403'):
                continue  # blocks curl, likely OK in browser
            if 'mtmessestand.com' in url:
                continue  # not deployed yet
            err(f"{code} → {url}")
        except Exception as e:
            warn(f"unreachable: {url}")
    if errors == 0:
        ok("All external links OK")

# ---------------------------------------------------------------------------
# 5. BLOG CARD LINK CHECK
# ---------------------------------------------------------------------------
print("\n=== 5. BLOG CARD LINKS ===")
for f in html_files:
    if 'index.html' not in str(f):
        continue
    content = f.read_text(encoding='utf-8', errors='ignore')
    # Check for broken card link patterns
    bad_patterns = [
        (r'href="en/', 'card links must use basename (blog/, not en/)'),
        (r'href="tr/', 'card links must use basename (blog/, not tr/)'),
    ]
    for pattern, msg in bad_patterns:
        matches = re.findall(pattern, content)
        if matches:
            err(f"{f.relative_to(BASE)}: {msg} — found {len(matches)}")

# ---------------------------------------------------------------------------
# SUMMARY
# ---------------------------------------------------------------------------
print(f"\n{'='*50}")
print(f"  ERRORS:   {errors}")
print(f"  WARNINGS: {warnings}")
print(f"  STATUS:   {'❌ FAIL' if errors > 0 else '✓ PASS'}")
print(f"{'='*50}")

sys.exit(1 if errors > 0 else 0)
