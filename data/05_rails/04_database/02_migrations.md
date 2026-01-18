---
title: マイグレーション
description: データベーススキーマ変更の管理方法
---

# マイグレーション

Railsのマイグレーションは、データベーススキーマの変更をバージョン管理する仕組みです。

## マイグレーションファイル

### 配置場所

```
db/migrate/
```

### ファイル命名規則

```
[タイムスタンプ]_[説明].rb
```

例：
```
db/migrate/20240118123000_create_users.rb
db/migrate/20240118124500_add_email_to_users.rb
db/migrate/20240118130000_remove_age_from_users.rb
```

### タイムスタンプ

形式: `YYYYMMDDHHmmss`

- 実行順序を保証
- コンフリクトを防止
- 自動生成されるため手動で作成不要

## マイグレーションの生成

### ジェネレータ

```bash
# テーブル作成
$ rails generate migration CreateUsers

# カラム追加
$ rails generate migration AddEmailToUsers email:string

# カラム削除
$ rails generate migration RemoveAgeFromUsers age:integer

# インデックス追加
$ rails generate migration AddIndexToUsersEmail

# 外部キー追加
$ rails generate migration AddUserRefToPosts user:references
```

### モデルと同時生成

```bash
$ rails generate model User name:string email:string
```

以下が同時に生成されます：
- モデル: `app/models/user.rb`
- マイグレーション: `db/migrate/[timestamp]_create_users.rb`
- テストファイル

## テーブル作成

### create_table

```ruby
# db/migrate/20240118000000_create_users.rb
class CreateUsers < ActiveRecord::Migration[7.0]
  def change
    create_table :users do |t|
      t.string :name
      t.string :email
      t.integer :age
      t.text :bio

      t.timestamps  # created_at, updated_at
    end
  end
end
```

### データ型

| メソッド | SQLの型 | 説明 |
|---------|---------|------|
| `t.string` | VARCHAR(255) | 短い文字列 |
| `t.text` | TEXT | 長い文字列 |
| `t.integer` | INTEGER | 整数 |
| `t.bigint` | BIGINT | 大きな整数 |
| `t.float` | FLOAT | 浮動小数点数 |
| `t.decimal` | DECIMAL | 固定小数点数 |
| `t.boolean` | BOOLEAN | 真偽値 |
| `t.date` | DATE | 日付 |
| `t.datetime` | DATETIME | 日時 |
| `t.timestamp` | TIMESTAMP | タイムスタンプ |
| `t.time` | TIME | 時刻 |
| `t.binary` | BLOB | バイナリデータ |
| `t.json` | JSON | JSON（PostgreSQL、MySQL 5.7+） |

### オプション

```ruby
create_table :users do |t|
  t.string :name, null: false              # NOT NULL
  t.string :email, default: ""             # デフォルト値
  t.integer :age, limit: 2                 # サイズ指定
  t.decimal :price, precision: 10, scale: 2  # 10桁、小数点以下2桁
  t.string :status, index: true            # インデックスを作成
end
```

## カラムの追加

### add_column

```ruby
# db/migrate/20240118000001_add_email_to_users.rb
class AddEmailToUsers < ActiveRecord::Migration[7.0]
  def change
    add_column :users, :email, :string
    add_column :users, :phone, :string, null: false, default: ""
  end
end
```

### 複数カラムを一度に追加

```ruby
class AddDetailsToUsers < ActiveRecord::Migration[7.0]
  def change
    add_column :users, :first_name, :string
    add_column :users, :last_name, :string
    add_column :users, :age, :integer
  end
end
```

## カラムの削除

### remove_column

```ruby
# db/migrate/20240118000002_remove_age_from_users.rb
class RemoveAgeFromUsers < ActiveRecord::Migration[7.0]
  def change
    remove_column :users, :age, :integer
  end
end
```

注意: ロールバック可能にするため、型の指定が推奨されます。

## カラムの変更

### change_column

```ruby
class ChangeEmailInUsers < ActiveRecord::Migration[7.0]
  def change
    change_column :users, :email, :string, null: false
  end
end
```

注意: `change_column` は自動ロールバックできないため、`up`/`down` の使用を検討してください。

### change_column_null

NULL制約の変更専用メソッド：

```ruby
class ChangeUsersEmailNull < ActiveRecord::Migration[7.0]
  def change
    change_column_null :users, :email, false  # NOT NULL制約を追加
  end
end
```

### change_column_default

デフォルト値の変更専用メソッド：

```ruby
class ChangeUsersStatusDefault < ActiveRecord::Migration[7.0]
  def change
    change_column_default :users, :status, from: nil, to: "active"
  end
end
```

## カラム名の変更

### rename_column

```ruby
class RenameEmailInUsers < ActiveRecord::Migration[7.0]
  def change
    rename_column :users, :email, :email_address
  end
end
```

## インデックス

### add_index

```ruby
class AddIndexToUsersEmail < ActiveRecord::Migration[7.0]
  def change
    add_index :users, :email, unique: true
  end
end
```

### 複合インデックス

```ruby
class AddIndexToUsers < ActiveRecord::Migration[7.0]
  def change
    add_index :users, [:last_name, :first_name]
  end
end
```

### remove_index

```ruby
class RemoveIndexFromUsers < ActiveRecord::Migration[7.0]
  def change
    remove_index :users, :email
  end
end
```

## 外部キー

### add_foreign_key

```ruby
class AddForeignKeyToUsers < ActiveRecord::Migration[7.0]
  def change
    add_foreign_key :posts, :users
    # posts.user_id に外部キー制約を追加
  end
end
```

### references を使った方法

```ruby
class CreatePosts < ActiveRecord::Migration[7.0]
  def change
    create_table :posts do |t|
      t.references :user, foreign_key: true
      # user_id カラム + 外部キー制約 + インデックスを作成
      t.string :title
      t.timestamps
    end
  end
end
```

## テーブルの削除

### drop_table

```ruby
class DropUsers < ActiveRecord::Migration[7.0]
  def change
    drop_table :users
  end
end
```

ロールバック可能にするには、ブロックでスキーマ定義：

```ruby
class DropUsers < ActiveRecord::Migration[7.0]
  def change
    drop_table :users do |t|
      t.string :name
      t.string :email
      t.timestamps
    end
  end
end
```

## up / down メソッド

### 自動ロールバックできない場合

`change` では自動ロールバックできない操作は、`up`/`down` を使用します。

```ruby
class ChangeProductsPrice < ActiveRecord::Migration[7.0]
  def up
    change_column :products, :price, :string
  end

  def down
    change_column :products, :price, :integer
  end
end
```

### データ操作を含む場合

```ruby
class AddAdminToUsers < ActiveRecord::Migration[7.0]
  def up
    add_column :users, :admin, :boolean, default: false
    User.update_all(admin: false)
  end

  def down
    remove_column :users, :admin
  end
end
```

## マイグレーションの実行

### 実行

```bash
# すべての未実行マイグレーションを実行
$ rails db:migrate

# 特定のバージョンまで実行
$ rails db:migrate VERSION=20240118000000

# 環境指定
$ RAILS_ENV=production rails db:migrate
```

### ロールバック

```bash
# 最後のマイグレーションを取り消し
$ rails db:rollback

# 3つ前まで戻す
$ rails db:rollback STEP=3

# 特定のバージョンまで戻す
$ rails db:migrate VERSION=20240118000000
```

### やり直し

```bash
# 最後のマイグレーションをやり直し
$ rails db:migrate:redo

# 3つ分やり直し
$ rails db:migrate:redo STEP=3
```

### ステータス確認

```bash
$ rails db:migrate:status

database: myapp_development

 Status   Migration ID    Migration Name
--------------------------------------------------
   up     20240118000000  Create users
   up     20240118000001  Add email to users
  down    20240118000002  Remove age from users
```

## schema.rb

### 自動生成されるスキーマファイル

```
db/schema.rb
```

マイグレーション実行後、現在のデータベース構造が自動的に記録されます。

```ruby
# db/schema.rb
ActiveRecord::Schema[7.0].define(version: 2024_01_18_000001) do
  create_table "users", force: :cascade do |t|
    t.string "name"
    t.string "email"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
end
```

### スキーマからDBを作成

```bash
$ rails db:schema:load
```

すべてのマイグレーションを実行する代わりに、`schema.rb` から直接データベースを作成できます（高速）。

## まとめ

- マイグレーションファイルは `db/migrate/` に配置
- ファイル名はタイムスタンプで自動採番
- `rails generate migration` でファイルを生成
- `change` メソッドで変更を記述（自動ロールバック）
- `up`/`down` メソッドで明示的にロールバックを定義
- `rails db:migrate` で実行、`rails db:rollback` で取り消し
- `db/schema.rb` に現在のスキーマが記録される
- マイグレーションは順序が保証され、チーム開発でも安全
