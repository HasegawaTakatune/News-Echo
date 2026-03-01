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

#### 5.3 ホットリロード（開発環境）

フロントエンドはホットモジュール交換（HMR）に対応しており、ファイル変更時に自動でブラウザが更新されます。

**ホットリロードの確認**：

1. ブラウザで `http://localhost:3000` を開く
2. `frontend/pages/login.vue` など任意のファイルを編集・保存
3. ブラウザが自動更新され、変更が反映される

**HMR設定詳細**（`nuxt.config.ts`）：

```typescript
vite: {
  server: {
    hmr: {
      host: 'localhost',
      port: 3000,
      protocol: 'ws',
    },
  },
}
```

Docker環境でのホットリロード機能が正常に動作するための設定です。

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

## 7. マイグレーション（詳細）

Laravel マイグレーションを使用してデータベーススキーマを管理しています。

### 7.1 マイグレーション実行

すべての保留中のマイグレーションを実行：

```bash
docker-compose exec app php artisan migrate
```

### 7.2 マイグレーション状態確認

実行済み・保留中のマイグレーションを確認：

```bash
docker-compose exec app php artisan migrate:status
```

以下のマイグレーションが `Ran` 状態であることを確認してください：

- `0001_01_01_000000_create_users_table`
- `0001_01_01_000001_create_cache_table`
- `0001_01_01_000002_create_jobs_table`
- `2025_02_16_000001_add_is_admin_to_users_table`
- `2025_02_16_000002_create_email_verification_tokens_table`
- `2025_02_16_000003_create_news_table`
- `2025_02_16_000004_create_settings_table`
- `2025_02_16_000005_create_personal_access_tokens_table`
- `2026_03_01_000001_add_last_posted_at_to_news_table` (ニュース投稿バッチ用)

### 7.3 マイグレーションのロールバック

最後に実行したバッチをロールバック：

```bash
docker-compose exec app php artisan migrate:rollback
```

特定数のバッチをロールバック：

```bash
docker-compose exec app php artisan migrate:rollback --step=3
```

すべてのマイグレーションをロールバック：

```bash
docker-compose exec app php artisan migrate:reset
```

### 7.4 マイグレーション再実行

テスト時にすべてをロールバックして再実行：

```bash
docker-compose exec app php artisan migrate:refresh
```

ロールバック + マイグレーション + シーダー実行：

```bash
docker-compose exec app php artisan migrate:refresh --seed
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

特定のサービスのログを確認：

```bash
docker-compose logs -f app    # バックエンド
docker-compose logs -f ui     # フロントエンド
docker-compose logs -f db     # データベース
```

---

## 11. 開発時の便利コマンド

### ホットリロード（Nuxt）

フロントエンドはホットモジュール交換（HMR）に対応しており、ファイル変更時に自動でブラウザが更新されます。

✓ 作成したコンポーネント、ページ、スタイルを編集・保存するだけで、変更箇所がリアルタイムで反映されます
✓ 開発中を続ける場合はサーバー起動時の一度のチェック同程度で算笠を仁▲ください

### Laravel マイグレーション管理

開発中のデータベーススキーマ管理用コマンド：

```bash
# 保留中のすべてのマイグレーションを実行
docker-compose exec app php artisan migrate

# マイグレーション紦史を確認
docker-compose exec app php artisan migrate:status

# 最後のバッチをロールバック
docker-compose exec app php artisan migrate:rollback

# 3バッチロールバック
docker-compose exec app php artisan migrate:rollback --step=3

# テスト時: 完全リセットして再実行
docker-compose exec app php artisan migrate:refresh
```

```bash
docker-compose exec app php artisan tinker
```

### データベースシェル

```bash
docker-compose exec db psql -U news_echo -d news_echo
```

### コンテナへの直接アクセス

開発物をデバッグしたい時、コンテナを直接操作できます：

```bash
# バックエンド (PHP) コンテナ
docker-compose exec app bash

# フロントエンド (Node.js) コンテナ
docker-compose exec ui bash
```

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

- **プロジェクト README**: [README.md](README.md)
- **プロジェクト構成**: [プロジェクト構成.txt](プロジェクト構成.txt)
- **ニュース投稿バッチについて**: `backend/app/Services/NewsBatchService.php` を参照
