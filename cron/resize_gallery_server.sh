#!/bin/bash

mysql -u admin -padmin123 -h 192.168.0.203 --skip-column-names -e "SELECT DISTINCT(club_id) as club_id from fairgate_migrate.fg_gm_items WHERE cron_executed='1' AND type='IMAGE'" | while read club_id;
do
    # Images are taken for resizing
    mysql -u admin -padmin123 -h 192.168.0.203 --skip-column-names -e  "update fairgate_migrate.fg_gm_items set cron_executed='2' WHERE club_id=$club_id AND cron_executed='1' AND type='IMAGE'";
    mysql -u admin -padmin123 -h 192.168.0.203 --skip-column-names -e "SELECT BINARY(fairgate_migrate.fg_gm_items.filepath) as images FROM fairgate_migrate.fg_gm_items WHERE cron_executed = '2' AND club_id =$club_id AND type='IMAGE'" | while read images
    do 
        for m in ${images}  ;
        do
            if [ -f "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$m" ] 
            then
                i="/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$m"
                original=$m
                temp=$m

                # if gif image then coalesce
                case "$m" in
                *".gif")
                   temp = "temp-$original"
                 gm convert "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$original" -coalesce "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp";
                esac

                #exif auto orient images
                gm convert "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" -auto-orient "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp";

                dim1=`php /var/www/html/fairgate_fedv2_qa/fairgate4/app/console gallery:resize --file=$i --maxHeight=1080 --maxWidth=1920`
                gm convert  "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" +dither -resize $dim1 "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_1920/$original";

                dim2=`php /var/www/html/fairgate_fedv2_qa/fairgate4/app/console gallery:resize --file=$i --maxHeight=300 --maxWidth=300`
                gm convert  "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" +dither -resize $dim2 "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_300/$original";

                dim3=`php /var/www/html/fairgate_fedv2_qa/fairgate4/app/console gallery:resize --file=$i --maxHeight=100 --maxWidth=100`
                gm convert  "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" +dither -resize $dim3 "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_100/$original";

                gm convert  "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" +dither -resize 580x\> "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_580/$original";
                
                gm convert  "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" +dither -resize 1140x\> "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_1140/$original";
                
                case "$m" in
                *".gif") 
                    rm -f '/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp';
                esac
            fi
        done
    done

    mysql -u admin -padmin123 -h 192.168.0.203 --skip-column-names -e "update fairgate_migrate.fg_gm_items set cron_executed='0' WHERE club_id=$club_id AND cron_executed='2'";

done;