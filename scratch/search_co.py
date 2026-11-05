with open("d:/laragon/www/simawa/public/gowa-ui.html", "r", encoding="utf-8") as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'function CO\(|const CO=|var CO=', text)]
for idx in matches:
    start = max(0, idx - 100)
    end = min(len(text), idx + 400)
    print("="*60)
    print(text[start:end])
