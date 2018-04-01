FROM mahmad2504/agc-baseimage
MAINTAINER Mumtaz Ahmad <ahmad-mumtaz1@hotmail.com>
RUN rm -fr /app
ADD src /app
ADD conf/rewrite.load /etc/apache2/mods-enabled
RUN rm /etc/apache2/apache2.conf
ADD conf/apache2.conf /etc/apache2/apache2.conf
