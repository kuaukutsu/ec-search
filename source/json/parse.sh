#!/bin/bash

# Проверка наличия jq
if ! command -v jq &> /dev/null; then
    echo "Ошибка: jq не установлен." >&2
    exit 1
fi

jq -r '
  (map(keys) | add | unique) as $json_keys |
  range(0; length) as $i |
  [
    ($i + 1),
    (.[$i] as $row | $json_keys[] | $row[.])
  ] |
  # Превращаем в TSV, заменяя null на пустые строки и экранируя спецсимволы
  map(if . == null then "" else . end | if type == "array" or type == "object" then tojson else . end) | @tsv
'
