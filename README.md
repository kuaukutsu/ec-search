# Example Code: Full-text search engine (FTS)

Цель: собрать тестовый стенд на базе двух поисковых движков: ManticoreSearch и OpenSearch, 
для проверки гипотез, обкатки решений, и покрутить ручки конфигураций.
В качестве источника: популярыне СУБД (postgres и mysql), и json для организации описка по документам, 
в том числе и html.

## Settings

**logs** `runtime/manticore/logs{query,searchd}`
**source** `source{json,sql}`

## Build

```shell
make build
```

```shell
make up
```

Миграции

```shell
make composer-up
```

```shell
make migrate-init
```

```shell
make migrate-up
```

## Run

```shell
make app
```

```shell
php app/search.php migrate:{pg,my,json} word
```

## Manticore

### Нюансы

Если слово «*» входит в список стоп-слов или после обработки морфологией (стеммингом) превращается в пустой токен,
запрос становится «пустым». Пустой полнотекстовый запрос в Manticore часто трактуется как match_all, то есть поиск без
фильтрации, что возвращает все записи.

`stopwords = /usr/share/manticore/stopwords/ru`

### Ссылки

- https://github.com/manticoresoftware/docker
- https://github.com/manticoresoftware/manticoresearch
- https://github.com/manticoresoftware/manticoresearch-php
- https://github.com/manticoresoftware/manticoresearch-buddy

## OpenSearch
