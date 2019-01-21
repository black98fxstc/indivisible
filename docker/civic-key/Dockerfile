FROM node

COPY IO.js .
COPY KEYS.js .
COPY simplify.js .
COPY GetCensusMaps.js .
COPY GetOpenStates.js .
COPY GetOpenStates2.js .
COPY GetCongress.js .
COPY GetCivicKey.js .

CMD [ "node", "--max-old-space-size=4096", "GetCivicKey.js" ]

USER www-data
