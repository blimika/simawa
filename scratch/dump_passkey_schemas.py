import urllib.request
import re

url = "https://raw.githubusercontent.com/aldinokemal/go-whatsapp-web-multidevice/refs/heads/main/docs/openapi.yaml"
data = urllib.request.urlopen(url).read().decode('utf-8')

for name in ['PasskeyStatusResponse', 'WebAuthnAssertion']:
    pattern = rf'    {name}:\n(.*?)(?=\n    [A-Za-z0-9_\-]+:|\n  [a-z]+:|\Z)'
    m = re.search(pattern, data, re.DOTALL)
    if m:
        print("="*60)
        print("SCHEMA:", name)
        print("="*60)
        print(m.group(0))
