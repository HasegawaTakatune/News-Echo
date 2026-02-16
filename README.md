# News-Echo

## 概要
ニュースによっては報道されて終わりではなく、その後どうなったかを見ていきたいニュースもあるかと思います。
そのため、登録したニュースから最新情報の収集とSNS投稿を行うWebアプリ、「あのニュースは今どうなった！？（News Echo）」のプロジェクトです。

## 技術スタック
- **Frontend**: Nuxt 3 + TypeScript
- **Backend**: Laravel 12 + PHP 8.3
- **DB**: PostgreSQL 16
- **Infra**: Docker Compose, Nginx

## 起動方法

1. `.env.example` をコピーして `.env` を作成
2. `docker compose up -d` で起動
3. ブラウザで http://localhost にアクセス

### SSH接続（オプション）
- Backend: `ssh -p 2222 root@localhost` （`.env` の `SSH_PUBLIC_KEY` を設定）
- Frontend: `ssh -p 2223 root@localhost`

## 初期セットアップ
初回起動後、管理者ユーザを作成する場合:
```bash
docker compose exec app php artisan tinker
# > $u = \App\Models\User::first(); $u->is_admin = true; $u->save();
```

## 開発環境
- vscode
  - Laravel(PHP)
  - Nuxt(Vue/TypeScript)
- PostgreSQL
- Docker
