with open("d:/laragon/www/simawa/public/gowa-ui.html", "r", encoding="utf-8") as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'passkey', text, re.IGNORECASE)]
print(f"Found {len(matches)} matches for passkey:")
for idx in matches[:10]:
    start = max(0, idx - 150)
    end = min(len(text), idx + 350)
    print("="*60)
    print(text[start:end])
