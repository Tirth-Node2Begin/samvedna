from PIL import Image
import base64, io

img = Image.open('/mnt/data/Samvedna-Logo-2-removebg-preview.png')
w, h = img.size

buf = io.BytesIO()
img.save(buf, format="PNG")
b64 = base64.b64encode(buf.getvalue()).decode("ascii")

svg = f'''<svg xmlns="http://www.w3.org/2000/svg" width="{w*8}" height="{h*8}" viewBox="0 0 {w} {h}">
  <image href="data:image/png;base64,{b64}" width="{w}" height="{h}"/>
</svg>'''

out = "/mnt/data/Samvedna_logo_large_transparent.svg"
with open(out, "w", encoding="utf-8") as f:
    f.write(svg)

print(out)
