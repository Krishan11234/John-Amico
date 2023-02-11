## pull official base image
#FROM tnir/mysqlclient

#FROM python:3.8.5-alpine3.12
#
#RUN apk add --update --no-cache mariadb-connector-c-dev \
#	&& apk add --no-cache --virtual .build-deps \
#		mariadb-dev \
#		gcc \
#		musl-dev \
#    && pip install mysqlclient==1.4.6 \
#	&& apk del .build-deps
#
#
#### These packages needs to be installed for "Pillow"
#RUN apk add zlib-dev jpeg-dev gcc musl-dev

FROM omarsharif/mysqlclient1.4.6_pillow7.2.0:1.0

### These packages needs to be installed for "Pillow"
RUN apk add zlib-dev jpeg-dev gcc musl-dev
RUN apk add libxml2-dev libxslt-dev

# set work directory
WORKDIR /usr/src/app

# Create a non-root user and switch to it
RUN adduser --disabled-password --gecos '' --shell /bin/bash user \
 && chown -R user:user /usr/src/app
#RUN echo "user ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/90-user
USER user

# All users can use /home/user as their home directory
ENV HOME=/home/user
RUN chmod 777 /home/user


## Add crontab file in the cron directory
#ADD crontab /etc/cron.d/hello-cron
#
## Give execution rights on the cron job
#RUN chmod 0644 /etc/cron.d/hello-cron
#
## Create the log file to be able to run tail
#RUN touch /var/log/cron.log
#
##Install Cron
#RUN apt-get -y install cron
#
## Run the command on container startup
#CMD cron && tail -f /var/log/cron.log


# set environment variables
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1

# install dependencies
RUN pip install --upgrade pip
RUN /usr/local/bin/python -m pip install --upgrade pip
COPY ./requirements /usr/src/app/requirements

RUN pip install -r requirements/development.txt
# RUN pip install -r requirements/producttion.txt

# copy entrypoint.sh
COPY ./entrypoint.sh /usr/src/app/entrypoint.sh

# copy project
COPY . /usr/src/app/

# run entrypoint.sh
ENTRYPOINT ["/usr/src/app/entrypoint.sh"]
