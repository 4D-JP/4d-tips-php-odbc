# 4d-tips-php-odbc
PHPとODBCを使用して4Dにアクセスする例題です。

概要
--
[PHP](https://ja.wikipedia.org/wiki/PHP:_Hypertext_Preprocessor)は，Mac, Windows, Linuxで実行できるプログラミング言語、およびその言語処理系です。PHPを使用すれば，4Dのデータベースに外部からアクセスするプログラムを記述することができます。

この例題では，PHPから4Dにアクセスする方法として[ODBC](https://ja.wikipedia.org/wiki/Open_Database_Connectivity)を使用します。そのためには，下記の条件が満たされていなければなりません。

1. PHPにODBC拡張モジュールが含まれている
2. 4D ODBC Driverがセットアップされている

この例題では，MacおよびWindowsのPHPから4DにODBCでアクセスする例を考慮します。

**注記**：Linux版の4D ODBC Driverは提供されていません。Linux版のPHPから4Dにアクセスしたい場合，PDO_4DまたはSQL以外の手段（HTTP, SOAP, REST）を検討しなければなりません。

Mac
---

MacにはPHPがプリインストールされていますが，OS X 10.9以降，[ODBC拡張モジュールが含まれていない](https://community.intersystems.com/post/using-odbc-php-os-x-109-mavericks)ようです。また，Mac向けの実行ファイルは配付されていないので，ソースコードからODBC拡張モジュールが追加されたPHPをビルドするか，プリインストールされているPHPでもODBC拡張が認識されるようにしなければなりません。

**注記**：PHPのODBC拡張には，``iODBC``，``unixODBC``など，いくつかの実装が存在します。たとえば，下記の手順で``unixODBC``のODBC拡張が含まれたPHPをビルドすることができますが，これで4Dに接続しようとすると，文字コードが正しく処理されません。（UTF-16がUTF-32として処理されているような印象）

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
cd ~/Downloads/php-5.5.38 
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

###ODBCドライバーのインストール

Mac版の4D ODBC Driverは，2種類が提供されています。

* macOS 32-bit
* macOS 64-bit

ODBCドライバーは，クライアント（たとえばPHPを実行するシステム）側のプラットフォームに合ったものをクライアント側にインストールします。PHPは63ビットアプリケーションですが，32ビット版の4DからODBCの接続テストが実行できるように，両方のドライバーをインストールすることにします。

ドライバーをダウンロードします。

http://www.4d.com/jp/downloads/products.html

Rバージョンとそうでないバージョンでは，ODBCドライバーが違います。ここでは，両方のドライバーをインストールすることにします。

ファイルを展開し，所定のフォルダーに「4D ODBC x32.bundle」または「4D ODBC x64.bundle」を移動します。Rバージョンとそうでないバージョンは，ファイル名が同じなので，``/Library/ODBC``にサブディレクトリを作成してそこに移動します。

``/Library/ODBC/15/4D ODBC x32.bundle``

``/Library/ODBC/15/4D ODBC x64.bundle``

``/Library/ODBC/15R/4D ODBC x32.bundle``

``/Library/ODBC/15R/4D ODBC x64.bundle``

次に``/Library/ODBC/odbc.ini``ファイルを編集しますが，[v15](http://doc.4d.com/4Dv15/4D/15.2/Installing-an-ODBC-driver-on-OS-X.300-2885364.ja.html)と[v15R](http://doc.4d.com/4Dv15R5/4D/15-R5/Installing-an-ODBC-driver-on-OS-X.300-3014275.ja.html)では記述する内容が違います。

両方を併記すると下記のようになります。

```
[ODBC Data Sources]
4D_V15_32   = 4D v15 ODBC Driver 32-bit
4D_V15_64   = 4D v15 ODBC Driver 64-bit
4D_v15RX_32 = 4D v15 Rx ODBC Driver 32-bit
4D_v15RX_64 = 4D v15 Rx ODBC Driver 64-bit

[4D_V15_32]
Driver      = /Library/ODBC/15/4D ODBC x32.bundle/Contents/MacOS/4D ODBC x32
Description = 4D v15 32 bits

[4D_V15_64]
Driver      = /Library/ODBC/15/4D ODBC x64.bundle/Contents/MacOS/4D ODBC x64
Description = 4D v15 64 bits

[4D_v15RX_32]
Driver      = /Library/ODBC/15R/4D ODBC x32.bundle/Contents/MacOS/4D ODBC x32
Description = 4D v15 Rx 32 bits

[4D_v15RX_64]
Driver      = /Library/ODBC/15R/4D ODBC x64.bundle/Contents/MacOS/4D ODBC x64
Description = 4D v15 Rx 64 bits
```

ドライバーをインストールしたら，ODBC管理ツールを起動してデータベースの名前（DSN）を登録します。

前の手順ですでにiODBCのODBCアドミニストレーターがインストールされているはずです。

あるいは，別のODBCアドミニストレーターを使用することもできます。

* サードパーティODBCマネージャー

http://www.odbcmanager.net/faq.php

アプリケーション > ユーティリティ にインストールされます。

iODBCのODBCアドミニストレーターは，32ビット版と64ビット版が用意されています。どちらのアドミニストレーターでも32ビット版と64ビット版のDSNを登録することができますが，接続テストは，それぞれのiODBC管理ツールで実行する必要があります。

![odbc-admin-mac](https://cloud.githubusercontent.com/assets/10509075/20777112/37c54e44-b7a8-11e6-9ac4-28b973ce8e95.png)

**注記**: ODBC Managerのほうは接続テストができないようです。

![odmc-manager-mac](https://cloud.githubusercontent.com/assets/10509075/20777173/980bfa14-b7a8-11e6-9beb-8f8418742ee2.png)

###SQLサーバーの動作を確認する

接続テストのために簡単な4Dデータベースを作成します。

まず一般エラースタックを記録するためのテーブルを作成します。

![generic-error-table](https://cloud.githubusercontent.com/assets/10509075/20777465/8269cdce-b7aa-11e6-9708-36a67eceafc1.png)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE base SYSTEM "http://www.4d.com/dtd/2007/base.dtd" >
<base>
	<table name="GENERIC_ERROR" uuid="9342ECC01D554F908DBD35D271A4A023" id="3">
		<field name="ID" uuid="7D27F6C8EEFC4EBEA21981A36AA62379" type="4" unique="true" autosequence="true" not_null="true" id="1">
			<field_extra/>
		</field>
		<field name="errCode" uuid="EBA4148C64D8475EA78A4CF9950F30A2" type="4" never_null="true" id="2">
			<field_extra/>
		</field>
		<field name="errComp" uuid="BA1CCA1507764A22B60CF8500BBEC348" type="10" never_null="true" id="3">
			<field_extra/>
		</field>
		<field name="errText" uuid="E6B4C1D0429C463BA30F0D513667E551" type="10" never_null="true" id="4">
			<field_extra/>
		</field>
		<primary_key field_name="ID" field_uuid="7D27F6C8EEFC4EBEA21981A36AA62379"/>
		<table_extra>
			<editor_table_info displayable_fields_count="4">
				<color red="207" green="209" blue="165" alpha="255"/>
				<coordinates left="540.73828125" top="31.75" width="141" height="125.33203125"/>
			</editor_table_info>
		</table_extra>
	</table>
</base>
```
一般エラー処理メソッドを作成します。

```
ARRAY LONGINT($codes;0)
ARRAY TEXT($comps;0)
ARRAY TEXT($texts;0)

GET LAST ERROR STACK($codes;$comps;$texts)

ARRAY TO SELECTION(\
$codes;[GENERIC_ERROR]errCode;\
$comps;[GENERIC_ERROR]errComp;\
$texts;[GENERIC_ERROR]errText)
```

[On SQL Authenticationデータベースメソッド](http://doc.4d.com/4Dv15R5/4D/15-R5/On-SQL-Authentication-Database-Method.300-2936650.ja.html)を作成します。

```
C_TEXT($1;$user)
C_TEXT($2;$password)
C_TEXT($3;$address)
C_BOOLEAN($0)

$user:=$1
$password:=$2
$address:=$3

ON ERR CALL("GENERIC_ERROR")
CHANGE CURRENT USER($user;$password)
ON ERR CALL("")

$0:=(OK=1)
```

パススルー（ODBCを介さない直接的なSQL接続）でSQLサーバーの動作（接続および認証）を確認します。

```
START SQL SERVER

ON ERR CALL("GENERIC_ERROR")
SQL LOGIN("IP:127.0.0.1";"Designer";"")
ON ERR CALL("")

If (OK=1)
	ALERT("OK")
	
	SQL LOGOUT
Else 
	ALERT("KO")
End if 
```

**注記**: ここでは，サーバー側・クライアント側ともに共通の汎用エラー処理メソッドとエラースタックコマンドを使用しています。クライアント側のODBC/SQLエラー情報コマンド[SQL GET LAST ERROR](http://doc.4d.com/4Dv15R5/4D/15-R5/SQL-GET-LAST-ERROR.301-2936663.ja.html)はODBC/パススルー接続ともに何も値を返さないようです。

接続に成功したら，今度は不正なパスワードを使用した場合に接続が拒否されることを確認します。

SQLサーバーの動作と認証をチェックすることができました。

###ODBCドライバーの動作を確認する

4Dをもうひとつ起動し，今度はODBC接続のテストを実行します。

ここではv15の32ビット版データソース名を指定しています。
```
SQL LOGIN("ODBC:4D_V15_32";"Designer";"")

If (OK=1)
	ALERT("OK")
	
	SQL LOGOUT
Else 
	ALERT("KO")
End if  
```

**注記**: パススルーとは違い，ODBCの自己接続はできないようです。アプリケーションがフリーズします。

接続に成功したら，今度はSQL命令のテストを実行します。

まずテスト用のテーブルを追加します。

![table_1](https://cloud.githubusercontent.com/assets/10509075/20778460/2342860e-b7b1-11e6-8442-016bd8c71220.png)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE base SYSTEM "http://www.4d.com/dtd/2007/base.dtd" >
<base>
	<table name="Table_1" uuid="E79A7B1B625746ACB8681E286A6E4156" id="1">
		<field name="ID" uuid="56341A917A064D9F961BA171E68AF660" type="4" unique="true" autosequence="true" not_null="true" id="1">
			<field_extra/>
		</field>
		<field name="Field_2" uuid="E955B64244D14E62A4180D342D07F095" type="10" limiting_length="255" never_null="true" id="2">
			<field_extra/>
		</field>
		<primary_key field_name="ID" field_uuid="56341A917A064D9F961BA171E68AF660"/>
		<table_extra>
			<editor_table_info displayable_fields_count="2">
				<color red="168" green="206" blue="226" alpha="255"/>
				<coordinates left="121.10546875" top="13.1171875" width="95" height="83.33203125"/>
			</editor_table_info>
		</table_extra>
	</table>
</base>
```

```
SQL LOGIN("ODBC:4D_V15_32";"Designer";"";*) //*: apply to BeginSQL~End SQL

If (OK=1)

	ARRAY TEXT($Field_2;0)
	ARRAY TEXT($values;3)
	
	$values{1}:="あいうえお"  //string literal gets corrupted in ODBC
	$values{2}:="かきくけこ"
	$values{3}:="さしすせそ"
	
	Begin SQL
		
		INSERT 
		INTO Table_1 (Field_2) 
		VALUES (:$values);
		
		SELECT Field_2 
		FROM Table_1
		INTO :$Field_2;
		
	End SQL
	
	SQL LOGOUT
Else 
	ALERT("KO")
End if  
```

**注記**: [SQL LOGIN](http://doc.4d.com/4Dv15R5/4D/15-R5/SQL-LOGIN.301-2936651.ja.html)で接続した外部データソースに対して``Begin SQL``~``End SQL``で命令を発行するためには，オプションの引数``*``を指定します。パススルーであれば，``Begin SQL``~``End SQL``ブロック内で文字列リテラルを指定することもできますが，ODBCドライバー経由では文字化けが発生するようです。またODBC経由ではSQL内にコメントが記述できないようです。

* パススルーでは有効でもODBCでは失敗するコード例

```sql
INSERT 
INTO Table_1 (Field_2) 
VALUES ('あいうえお'), ('かきくけこ'), ('さしすせそ');
```

SQLおよびODBCの動作が確認できたので，いよいよPHPで4Dにアクセスします。

PHPコードが記述されたファイルを作成します。

```php
<?php

putenv("ODBCINSTINI=/Library/ODBC/odbcinst.ini");
putenv("ODBCINI=/Library/ODBC/odbc.ini");

$connect = odbc_connect("4D_v15_64", "Designer", "");

$insert = "INSERT INTO Table_1 (Field_2) VALUES ('あいうえお'), ('かきくけこ'), ('さしすせそ')";
$result = odbc_do($connect, $insert);

$select = "SELECT Field_2 FROM Table_1";
$result = odbc_do($connect, $select);
while(odbc_fetch_row($result)){
        for($i=1;$i<=odbc_num_fields($result);$i++){
        echo odbc_result($result,$i)."\n";
    }
}

odbc_close($connect);
```

PHPコマンドにファイルパスを指定してコードを実行します。

```
php -f /Users/miyako/Desktop/example.php 
```

PHPとODBCで4Dのアクセスすることができました。

Windows
---
