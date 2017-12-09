#! /bin/bash

while read user mail web
do
./v-add-user $user $user $mail
./v-add-web-domain $user $web
done < /home/manuel/usuario_distancia.txt




