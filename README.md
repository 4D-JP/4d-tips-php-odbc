# 4d-tips-php-odbc
PHPとODBCを使用して4Dにアクセスする例題です。

概要
==
[PHP](https://ja.wikipedia.org/wiki/PHP:_Hypertext_Preprocessor)は，Mac, Windows, Linuxで実行できるプログラミング言語、およびその言語処理系です。PHPを使用すれば，4Dのデータベースに外部からアクセスするプログラムを記述することができます。

この例題では，PHPから4Dにアクセスする方法として[ODBC](https://ja.wikipedia.org/wiki/Open_Database_Connectivity)を使用します。そのためには，下記の条件が満たされていなければなりません。

1. PHPにODBC拡張モジュールが含まれている
2. 4D ODBC Driverがセットアップされている

この例題では，MacおよびWindowsのPHPから4DにODBCでアクセスする例を考慮します。

**注記**：Linux版の4D ODBC Driverは提供されていません。Linux版のPHPから4Dにアクセスしたい場合，PDO_4DまたはSQL以外の手段（HTTP, SOAP, REST）を検討しなければなりません。

Mac
---

MacにはPHPがプリインストールされていますが，OS X 10.9以降，[ODBC拡張モジュールが含まれていない](https://community.intersystems.com/post/using-odbc-php-os-x-109-mavericks)ようです。また，Mac向けの実行ファイルは配付されていないので，ソースコードからODBC拡張モジュールが追加されたPHPをビルドするか，プリインストールされているPHPでもODBC拡張が認識されるようにしなければなりません。

**注記**：PHPのODBC拡張には，``iODBC``，``unixODBC``など，いくつかの実装が存在します。たとえば，下記の手順で``unixODBC``のODBC拡張がPHPをビルドすることができますが，これで4Dに接続しようとすると，文字コードが正しく処理されませんでした。（UTF-16がUTF-32として処理されているような印象）

1. homebrewのインストール
http://brew.sh

2. unixodbcのインストール
```
brew update
brew install unixodbc
```

3. PHPのインストール
```
brew tap homebrew/dupes
brew tap homebrew/versions
brew tap homebrew/homebrew-php
brew options php56
```

https://ryanwinchester.ca/posts/install-php-5-6-in-osx-10-with-homebrew

この例題では``iODBC``のODBC拡張をMacにプリインストールされているPHPで使用する例を考慮します。

###iODBCのダウンロード

ダウンロードページにアクセスします。

http://www.iodbc.org/dataspace/doc/iodbc/wiki/iodbcWiki/Downloads

ページ内のアンカー（リンク）が切れているようなので，直接「Mac OS X」のセクションまでスクロールして移動します。この記事を書いている時点で，最新の安定バージョンは``3.52.12``です。

ソースコードからビルドすることもできますが，``mxkozzzz.dmg (iODBC SDK)``をダウンロードすれば，フレームワークとODBCアドミニストレーターの両方をインストールすることができます。

* フレームワークの場所
```
/Library/Frameworks/iODBC.framework
```
* ライブラリの場所
```
/usr/local/iODBC
```
* アドミニストレーターの場所
```
/Applications/iODBC
```

###ODBC拡張のインストール

まずMacにプリインストールされているPHPのバージョンを確認します。

```
php -v
```

```
PHP 5.5.38 (cli) (built: Aug 21 2016 21:48:49) 
Copyright (c) 1997-2015 The PHP Group
Zend Engine v2.5.0, Copyright (c) 1998-2015 Zend Technologies
```

同じバージョンのPHPソースコードをダウンロードします。

http://php.net/downloads.php

ダウンロードしたファイルを展開し，ターミナルを起動してカレントディレクトリを変更します。
```
cd /Users/miyako/Downloads/php-5.5.38 
```
ODBC拡張のディレクトリに移動します。
```
cd ext/odbc
```
PHPモジュールを作成する準備をします。
```
phpize
```

前の手順でインストールしたiODBCフレームワークにリンクするようにコンパイラとリンカのフラグを設定します。

```
CPPFLAGS='-DHAVE_IODBC -I/usr/local/iODBC/include' LDFLAGS='-L/usr/local/iODBC/lib -liodbc -liodbcinst' ODBC_TYPE=iodbc ./configure --with-iodbc 
make
```

``odbc.la``および``odbc.so``が作られます。

本来であれば，ここで``sudo make install``して完了なのですが，実際にはエラーが返されます。

``/usr/lib/php/extensions/no-debug-non-zts-20121212/``のアクセス権がないためです。

El Capitan以降，System Integrity Protection (SIP) により，このディレクトリは書き換えられないようになっています。

http://apple.stackexchange.com/questions/208815/error-configuring-mcrypt-after-upgrading-to-el-capitan

代わりのインストール先を用意して，そこにODBC拡張をインストールします。

```
mkdir -p /usr/local/lib/php/extensions
sudo make EXTENSION_DIR=/usr/local/lib/php/extensions install
```

``php.ini``ファイルの場所を確認します。

```
php -r 'phpinfo();' | grep 'Configuration File (php.ini)'
```

``/etc``であることがわかります。

ファイルがないので，``php.ini.default``を複製して``php.ini``にファイル名を変更し，エディターで開きます。

ファイルの末尾にフルパスでODBC拡張の場所を追加します。

```
extension=/usr/local/lib/php/extensions/odbc.so
```

ODBCが認識されたことを確認します。

```
php -r 'phpinfo();' | more
```
プリインストールされたPHPでODBCが使用できるようになりました。
```
odbc

ODBC Support => enabled
Active Persistent Links => 0
Active Links => 0
ODBC library =>  
ODBC_INCLUDE =>  
ODBC_LFLAGS =>  
ODBC_LIBS =>  

Directive => Local Value => Master Value
odbc.allow_persistent => On => On
odbc.check_persistent => On => On
odbc.default_cursortype => Static cursor => Static cursor
odbc.default_db => no value => no value
odbc.default_pw => no value => no value
odbc.default_user => no value => no value
odbc.defaultbinmode => return as is => return as is
odbc.defaultlrl => return up to 4096 bytes => return up to 4096 bytes
odbc.max_links => Unlimited => Unlimited
odbc.max_persistent => Unlimited => Unlimited
```
