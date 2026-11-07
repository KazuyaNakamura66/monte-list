# おうちモンテ活動カタログ

WordPress ＋ オリジナルテーマ（`theme/monte-list`）。仕様は [docs/design.md](docs/design.md)、経緯・進捗は [docs/notes.md](docs/notes.md)。

## ローカル環境
必要: Docker Desktop, Node.js

```bash
npm install
npm start          # http://localhost:8888 （管理画面 /wp-admin）
npm run stop
npx wp-env run cli wp eval-file scripts/seed-sample.php   # サンプル記事を投入
```

ローカル用アカウント
| 権限 | ユーザー名 | パスワード |
|---|---|---|
| 管理者 | admin | password |
| 編集者 | editor | editor |

## 構成
- `theme/monte-list/inc/post-types.php` 投稿タイプ（教具・活動）と分類
- `theme/monte-list/inc/fields.php` 入力項目（SCF）・サイト設定
- `theme/monte-list/inc/default-terms.php` 初期の分類データ
- `theme/monte-list/inc/noindex.php` 検索エンジン除外
