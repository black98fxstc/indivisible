FROM node

RUN git clone https://github.com/black98fxstc/call-congress.git

WORKDIR call-congress

RUN npm install

COPY .env .

CMD [ "npm", "start" ]