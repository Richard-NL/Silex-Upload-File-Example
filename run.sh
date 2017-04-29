#!/bin/sh
#sh ./textcleaner -g -e normalize -f 40 -o 5 -s 2  /home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/cropped_IMG_4542.JPG output.png                

#convert /home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/cropped_IMG_4542.JPG -fill '#613029' -opaque black output2.png

#convert /home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/cropped_IMG_4542.JPG img.jpg -separate img_rgb_%d.jpg
#sh ./textcleaner -g -e normalize -f 40 -o 5 -s 2  img_rgb_2.jpg output_FIX.png
#convert /home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/cropped_IMG_4542.JPG -fuzz 10% -fill black +opaque red -fill black -opaque red  output2.png

UPLOAD_PATH='/home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/'
sudo rm -rf ./web/upload/part_*_clean.png
sh ./textcleaner -g -e normalize -f 50 -o 2 -t 20 -p 20  "/home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/part_1.png"  "/home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/part_1_clean.png" 

#convert /home/richard/workspace/personal/Silex-Upload-File-Example/web/upload/cropped_IMG_4542.JPG -alpha off -fill black -opaque red -alpha on output2.png
