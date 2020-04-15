#!/usr/bin/env bash

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: $0 domain.name [days to expiration default=825]"
  exit 2
fi

DOMAIN=$1
DAYS=${2:-825}
SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
CERTDIR=~/.config/squareone/global/certs

if [ ! -d "${CERTDIR}" ]; then
  mkdir -p ${CERTDIR}
fi

cd "$SCRIPTDIR";

if [ ! -f "${CERTDIR}/tribeCA.key" ]; then
	echo "Generating certificate authority"

	openssl req -x509 -new -nodes -sha256 -newkey rsa:4096 -days ${DAYS} \
		-keyout "${CERTDIR}/tribeCA.key" \
		-out "${CERTDIR}/tribeCA.pem" \
		-subj "/C=US/ST=California/L=Santa Cruz/O=Modern Tribe/OU=Dev/CN=tri.be";

	if [[ $OSTYPE == darwin* ]]; then
		sudo security add-trusted-cert -d -r trustRoot -e hostnameMismatch -k /Library/Keychains/System.keychain "${CERTDIR}/tribeCA.pem";
	fi;
fi;

echo "Generating SSL certificate for $DOMAIN";

openssl req -new -nodes -sha256 -newkey rsa:4096 \
	-keyout "${CERTDIR}/${DOMAIN}.key" \
	-out "${CERTDIR}/${DOMAIN}.csr" \
	-subj "/C=US/ST=California/L=Santa Cruz/O=Modern Tribe/OU=Dev/CN=${DOMAIN}";

cat > "${CERTDIR}/${DOMAIN}.ext" <<-EOF
	authorityKeyIdentifier=keyid,issuer
	basicConstraints=CA:FALSE
	keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
	subjectAltName = @alt_names
	[alt_names]
	DNS.1 = ${DOMAIN}
	DNS.2 = *.${DOMAIN}
EOF

openssl x509 -req -days ${DAYS} -sha256 \
	-in "${CERTDIR}/${DOMAIN}.csr" \
	-CA "${CERTDIR}/tribeCA.pem" \
	-CAkey "${CERTDIR}/tribeCA.key" \
	-CAcreateserial \
	-extfile "${CERTDIR}/${DOMAIN}.ext" \
	-out "${CERTDIR}/${DOMAIN}.crt";

rm "${CERTDIR}/${DOMAIN}.ext";
