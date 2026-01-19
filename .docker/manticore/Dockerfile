FROM manticoresearch/manticore:15.1.0

# Arguments
ARG UID=1001

RUN <<EOF
    apt update &&
    apt install -y jq manticore-language-packs &&
    apt clean &&
    rm -rf /var/lib/apt/lists/*
EOF

# Add user
RUN <<EOF
    existing_user=$(getent passwd ${UID} | cut -d: -f1)
    if [ -z "$existing_user" ]; then
        adduser -u ${UID} -G manticore -s /bin/sh -D manticore
    fi
EOF

RUN <<EOF
  chown -R $UID:manticore /var/lib/manticore /usr/local/lib/manticore /etc/manticoresearch
EOF

USER $UID
