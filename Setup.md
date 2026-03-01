# News-Echo 環境構築ガイド

## 前提条件

このプロジェクトを運用するために、以下のツールがインストールされていることを確認してください：

- **Docker** (v24.0 以上)
- **Docker Compose** (v2.20 以上)
- **Git**
- **SSH キー** (オプション、bash での開発時に便利)

## セットアップ手順

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd News-Echo
```

### 2. 環境変数ファイルの作成

`.env.example` をコピーして `.env` ファイルを作成します：

```bash
cp .env.example .env
```

基本的な環境変数を設定します：

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=news_echo
DB_USERNAME=news_echo
DB_PASSWORD=secret

PGADMIN_DEFAULT_EMAIL=admin@example.com
PGADMIN_DEFAULT_PASSWORD=admin
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
docker-compose exec app php artisan migrate
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

#### 5.2 開発サーバーが自動で起動

`docker-compose up` 実行時に自動で起動します。  
一度だけ停止させて再起動した場合：

```bash
docker-compose exec ui npm run dev
```

### 6. ニュース投稿バッチの設定

ニュースの定期投稿機能を使う場合、以下のセットアップが必要です：

#### 6.1 マイグレーション確認

以下のマイグレーションが適用されていることを確認してください：

```bash
docker-compose exec app php artisan migrate:status
```

`2026_03_01_000001_add_last_posted_at_to_news_table` が `Ran` 状態であること。

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

## アクセス方法

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

### SSH 接続（開発環境）

#### バックエンド（PHP-FPM）コンテナへ接続

```bash
ssh -p 2222 root@localhost
```

#### フロントエンド（Nuxt）コンテナへ接続

```bash
ssh -p 2223 root@localhost
```

---

## トラブルシューティング

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

特定のサービスのログを確認：

```bash
docker-compose logs -f app    # バックエンド
docker-compose logs -f ui     # フロントエンド
docker-compose logs -f db     # データベース
```

---

## 開発時の便利コマンド

### ホットリロード（Nuxt）

フロントエンドが自動で再起動されます（コンテナ起動時）。

### Laravel Tinker（インタラクティブシェル）

```bash
docker-compose exec app php artisan tinker
```

### データベースシェル

```bash
docker-compose exec db psql -U news_echo -d news_echo
```

### コンテナへの直接アクセス

```bash
docker-compose exec app bash   # バックエンド
docker-compose exec ui bash    # フロントエンド
```

---

## 本番環境へのデプロイ

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

## その他

- **プロジェクト README**: [README.md](README.md)
- **プロジェクト構成**: [プロジェクト構成.txt](プロジェクト構成.txt)
- **ニュース投稿バッチについて**: `backend/app/Services/NewsBatchService.php` を参照
