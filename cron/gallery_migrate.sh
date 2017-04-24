#!/bin/bash

mysql -u admin -padmin123 -h 192.168.0.203 --skip-column-names -e "SELECT BINARY(fairgate_migrate.fg_gm_items.filepath) as image, club_id FROM fairgate_migrate.fg_gm_items WHERE type='IMAGE'" | while read image club_id
    do 
        if [ -f "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$image" ] 
            then
                mkdir -p "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_1140"

                i="/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$image"
                original=$image
                temp=$image

                # if gif image then coalesce
                case "$image" in
                *".gif")
                   temp = "temp-$original"
                  convert "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$original" -coalesce "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp";
                esac

                convert "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp" -resize 1140x "/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/width_1140/$original";

                case "$image" in
                *".gif") 
                    rm -f '/var/www/html/fairgate_fedv2_qa/web/uploads/$club_id/gallery/original/$temp';
                esac
            fi
    done