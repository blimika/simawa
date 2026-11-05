import urllib.request
import re

url = "https://raw.githubusercontent.com/aldinokemal/go-whatsapp-web-multidevice/refs/heads/main/docs/openapi.yaml"
data = urllib.request.urlopen(url).read().decode('utf-8')

for path in ['/app/passkey', '/app/passkey/response', '/app/passkey/confirm']:
    pattern = rf'  {path}:\n(.*?)(?=\n  /|\ncomponents:|\Z)'
    m = re.search(pattern, data, re.DOTALL)
    if m:
        print("="*60)
        print("PATH:", path)
        print("="*60)
        print(m.group(0))
