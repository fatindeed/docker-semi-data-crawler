# 半导体数据爬虫

[![Docker Stars](https://img.shields.io/docker/stars/fatindeed/semi-data-crawler.svg)](https://hub.docker.com/r/fatindeed/semi-data-crawler/) [![Docker Pulls](https://img.shields.io/docker/pulls/fatindeed/semi-data-crawler.svg)](https://hub.docker.com/r/fatindeed/semi-data-crawler/) [![Docker Automated build](https://img.shields.io/docker/automated/fatindeed/semi-data-crawler.svg)](https://hub.docker.com/r/fatindeed/semi-data-crawler/) [![Docker Build Status](https://img.shields.io/docker/build/fatindeed/semi-data-crawler.svg)](https://hub.docker.com/r/fatindeed/semi-data-crawler/)

[![Download size](https://images.microbadger.com/badges/image/fatindeed/semi-data-crawler.svg)](https://microbadger.com/images/fatindeed/semi-data-crawler "Get your own image badge on microbadger.com") [![Version](https://images.microbadger.com/badges/version/fatindeed/semi-data-crawler.svg)](https://microbadger.com/images/fatindeed/semi-data-crawler "Get your own version badge on microbadger.com") [![Source code](https://images.microbadger.com/badges/commit/fatindeed/semi-data-crawler.svg)](https://microbadger.com/images/fatindeed/semi-data-crawler "Get your own commit badge on microbadger.com")

## 用法

Run `docker stack deploy -c stack.yml mysql` (or `docker-compose -f stack.yml up`), wait for it to initialize completely, and visit `http://swarm-ip:8080`, `http://localhost:8080`, or `http://host-ip:8080` (as appropriate).

```sh
php run.php [options...] sub-command [args...] [--] [params]

Options:
  -f, --fresh      Fresh start with new database
  -h, --help       This help

  args...          Arguments passed to script

  params...        Parameters passed to script. Use -- when first parameter
```

## 示例

1.  Init all categories.

    ```sh
    php run.php initAllCategory
    ```


2.  Init all products with given manufacturer.

    ```sh
    php run.php initProductByManu infineon-technologies-ag
    ```


3.  Init all products with given manufacturer and product line.

    ```sh
    php run.php initProductByManuPL infineon-technologies-ag "IGBT Chip"
    ```


4.  Fresh start with new database and only init 10 rows.

    ```sh
    php run.php -f initProductByManu infineon-technologies-ag -- limit=10
    ```


5.  Fresh start with new database and only init 10 rows.

    ```sh
    php run.php initProductByPL "IGBT Chip" -- limit=10
    ```


6.  Dump json file for Tencent Cloud.

    ```sh
    php run.php dumpTxCloudJson
    ```

## 参考文档

- [SQLite Tutorial](http://www.sqlitetutorial.net/)
- [Datatypes In SQLite3](https://sqlite.org/datatype3.html)
- [SQL As Understood By SQLite](https://sqlite.org/lang.html)
