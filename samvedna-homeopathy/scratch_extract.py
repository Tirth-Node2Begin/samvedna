import fitz
import sys
import os

pdf_path = r"e:\EB projects\new project\Dr. Yakshika.pdf"
doc = fitz.open(pdf_path)
page = doc.load_page(0)
pix = page.get_pixmap(dpi=300)
output_path = r"e:\EB projects\new project\samvedna-homeopathy\public\images\dr-yakshika.jpg"
pix.save(output_path)
print("Saved image to", output_path)

text = page.get_text()
print("Text extracted:", text)
