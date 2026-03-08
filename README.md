# News-Echo

## 概要

ニュースによっては報道されて終わりではなく、その後どうなったかを見ていきたいニュースもあるかと思います。
そのため、登録したニュースから最新情報の収集とSNS投稿を行うWebアプリ、「あのニュースは今どうなった！？（News Echo）」のプロジェクトです。

また、AIによる開発を試してみます。

## 画面構成

- ログイン
- ダッシュボード
  - ニュース一覧
  - ニュース登録・更新（ダイアログ）
    - タイトル入力
    - 投稿間隔セレクトボックス（1ヶ月～1年）
  - ニュース削除確認（ダイアログ）
- 設定
  - パスワード更新
  - 投稿アカウント変更（管理者）
  - ニュースのリサーチプロンプト変更（管理者）

## 要件

登録したニュースタイトルから、当時からどの程進展しているか？または何も進んでいないのか？をリサーチしてSNSへ投稿します。
他者を誹謗中傷またはそれに類する内容が登録されていないかチェックします。

## 開発環境

- vscode
  - Laravel(PHP)
  - Nuxt(Vue/TypeScript)
- PostgreSQL
- pgAdmin 4
- Docker
- [Cursor](https://cursor.com/ja)
- Copilot（Raptor mini）

---

## セットアップ

## 前提条件

このプロジェクトを運用するために、以下のツールがインストールされていることを確認してください：

- **Docker** (v24.0 以上)
- **Docker Compose** (v2.20 以上)
- **Git**
- **SSH キー** (オプション、bash での開発時に便利)

## セットアップ手順

### 2. 環境変数ファイルの作成

`.env.example` をコピーして `.env` ファイルを作成します：

```bash
cp .env.example .env
```

### 3. Docker コンテナの起動

```bash
docker-compose up -d
```

コンテナが正常に起動したか確認します：

```bash
docker-compose ps
```

以下のサービスが `Running` 状態であることを確認：

- `web` (Nginx)
- `app` (PHP-FPM + Laravel)
- `ui` (Nuxt)
- `db` (PostgreSQL)
- `pgadmin` (pgAdmin)

### 4. バックエンド（Laravel）のセットアップ

#### 4.1 コンテナ内で移行を実行

```bash
docker-compose exec app php artisan migrate --seed
```

#### 4.2 アプリケーションキーの生成（初回のみ）

```bash
docker-compose exec app php artisan key:generate
```

#### 4.3 ストレージのパーミッション設定

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### 5. フロントエンド（Nuxt）のセットアップ

#### 5.1 依存パッケージのインストール

```bash
docker-compose exec ui npm install
```

#### 6.2 手動実行でテスト

```bash
docker-compose exec app php artisan news:post
```

#### 6.3 スケジューラの設定（本番環境）

`app/Console/Kernel.php` でコマンドをスケジュール登録し、  
毎晩 0 時に実行するよう設定してください：

```php
$schedule->command('news:post')->dailyAt('00:00');
```

その上で、以下の cron エントリをホストマシンまたはコンテナで実行：

```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. アクセス方法

### Web アプリケーション

- **フロントエンド（Nuxt）**: `http://localhost:3000`
- **API**: `http://localhost/api` （Nginx 経由）

### データベース管理

- **pgAdmin**: `http://localhost:8080`
  - メール: `admin@example.com`
  - パスワード: `admin`
  - サーバー: 自動登録済み（`News Echo DB`）
  - ユーザー名: `news_echo`
  - パスワード: `secret`

---

## 9. SSH 接続（開発環境）

#### バックエンド（PHP-FPM）コンテナへ接続

```bash
ssh -p 2222 root@localhost
```

#### フロントエンド（Nuxt）コンテナへ接続

```bash
ssh -p 2223 root@localhost
```

---

## 10. トラブルシューティング

### ポートが既に使用されている

別のプロセスがポート 80, 3000, 5432, 8080 などを使用している場合、  
`docker-compose.yml` でポートマッピングを変更してください。

例：

```yaml
ports:
  - "8000:80" # ホスト側を 8000 に変更
```

### パーミッションエラー

Laravel のストレージディレクトリにアクセス権限がない場合：

```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

### データベース接続エラー

PostgreSQL が起動していることを確認：

```bash
docker-compose exec db psql -U news_echo -d news_echo -c "SELECT 1;"
```

コマンドが成功すれば DB は正常です。

### コンテナログの確認

開発中に問題が発生した時：

```bash
# リアルタイムでログを追跡
docker-compose logs -f app    # バックエンド
docker-compose logs -f ui     # フロントエンド
docker-compose logs -f db     # データベース
docker-compose logs -f web    # Nginx

# 最近 50 行を表示
docker-compose logs --tail=50 app
```

---

## 12. 本番環境へのデプロイ

本番環境では以下の点を必ず変更してください：

1. **`.env` ファイルの値を本番用に更新**
   - `DB_PASSWORD`, `PGADMIN_DEFAULT_PASSWORD` など強力なパスワードに

2. **デバッグモードを無効化**

   ```env
   APP_DEBUG=false
   ```

3. **キャッシュクリア**

   ```bash
   docker-compose exec app php artisan config:cache
   docker-compose exec app php artisan route:cache
   ```

4. **SSL/TLS 設定**
   - Nginx 設定で HTTPS を有効化

5. **バッチジョブのスケジューラ設定**
   - Cron または同等のジョブスケジューラを構成

---

## 13. その他

- **プロジェクト構成**: [プロジェクト構成.txt](プロジェクト構成.txt)
- **ニュース投稿バッチについて**: `backend/app/Services/NewsBatchService.php` を参照

---

##### チェックリスト

- [x] 環境構築できているか？
- [x] テーブル定義・テストデータが作成されているか？
- [x] フロントエンド・バックエンドに分けて作成できているか？
- [ ] どの程度の精度でコード生成できているか？
- [ ] 細かな修正でどの程度差分が出るか？
