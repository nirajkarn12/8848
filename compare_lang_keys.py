from pathlib import Path

files = {
    'en': Path('inc/lang/en.php'),
    'ne': Path('inc/lang/ne.php'),
    'hi': Path('inc/lang/hi.php'),
}

keys = {}
for code, path in files.items():
    text = path.read_text(encoding='utf-8')
    found = []
    for line in text.splitlines():
        if '=>' in line:
            part = line.split('=>', 1)[0].strip()
            if (part.startswith("'") and part.endswith("'")) or (part.startswith('"') and part.endswith('"')):
                found.append(part[1:-1])
    keys[code] = set(found)
    print(code, len(found))

all_keys = set().union(*keys.values())
for code in files:
    missing = sorted(all_keys - keys[code])
    print('MISSING in', code, len(missing))
    print('\n'.join(missing))
