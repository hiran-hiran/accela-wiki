---
title: バリデーションとコールバック
description: 自動実行される検証と処理
---

# バリデーションとコールバック

Railsでは、データの検証（バリデーション）とライフサイクルイベント（コールバック）が自動的に実行されます。

## バリデーション

### 自動実行のタイミング

バリデーションは以下のメソッド実行時に自動的に行われます。

```ruby
user.save       # バリデーション実行 → 失敗時は false を返す
user.create     # バリデーション実行 → 失敗時は false を返す
user.update     # バリデーション実行 → 失敗時は false を返す
user.save!      # バリデーション実行 → 失敗時は例外発生
user.create!    # バリデーション実行 → 失敗時は例外発生
user.update!    # バリデーション実行 → 失敗時は例外発生
```

### 基本的なバリデーション

```ruby
# app/models/user.rb
class User < ApplicationRecord
  validates :name, presence: true
  validates :email, presence: true, uniqueness: true
  validates :age, numericality: { greater_than: 0 }
  validates :website, format: { with: URI::DEFAULT_PARSER.make_regexp }
end
```

### バリデーションの種類

| バリデーション | 用途 | 例 |
|--------------|------|-----|
| `presence` | 必須 | `validates :name, presence: true` |
| `uniqueness` | 一意性 | `validates :email, uniqueness: true` |
| `length` | 文字数 | `validates :password, length: { minimum: 6 }` |
| `numericality` | 数値 | `validates :age, numericality: { only_integer: true }` |
| `format` | 形式 | `validates :email, format: { with: /\A[\w+\-.]+@[a-z\d\-.]+\.[a-z]+\z/i }` |
| `inclusion` | 含まれる | `validates :role, inclusion: { in: %w[admin user guest] }` |
| `exclusion` | 含まれない | `validates :subdomain, exclusion: { in: %w[www admin] }` |
| `confirmation` | 確認 | `validates :password, confirmation: true` |
| `acceptance` | チェックボックス | `validates :terms, acceptance: true` |

### エラーメッセージ

```ruby
user = User.new
user.save  # => false

user.errors.any?  # => true
user.errors.full_messages  # => ["Name can't be blank", "Email can't be blank"]
user.errors[:name]  # => ["can't be blank"]
```

### カスタムバリデーション

```ruby
class User < ApplicationRecord
  validate :email_domain_allowed

  private

  def email_domain_allowed
    return if email.blank?

    domain = email.split('@').last
    unless ['example.com', 'test.com'].include?(domain)
      errors.add(:email, 'domain is not allowed')
    end
  end
end
```

## コールバック

### ライフサイクル

コールバックは、オブジェクトのライフサイクルの特定のタイミングで自動実行されます。

```ruby
class User < ApplicationRecord
  before_validation :normalize_email
  after_validation :log_errors
  before_save :encrypt_password
  after_save :send_welcome_email
  before_create :generate_token
  after_create :create_profile
  before_update :check_permissions
  after_update :notify_changes
  before_destroy :check_dependencies
  after_destroy :cleanup_files
end
```

### 主要なコールバック

#### 作成時

```
before_validation
after_validation
before_save
before_create
[データベース保存]
after_create
after_save
after_commit
```

#### 更新時

```
before_validation
after_validation
before_save
before_update
[データベース保存]
after_update
after_save
after_commit
```

#### 削除時

```
before_destroy
[データベース削除]
after_destroy
after_commit
```

### before_save の例

```ruby
class User < ApplicationRecord
  before_save :normalize_email

  private

  def normalize_email
    self.email = email.downcase.strip if email.present?
  end
end
```

```ruby
user = User.new(email: '  JOHN@EXAMPLE.COM  ')
user.save
user.email  # => "john@example.com"（自動的に正規化された）
```

### after_create の例

```ruby
class User < ApplicationRecord
  after_create :send_welcome_email

  private

  def send_welcome_email
    UserMailer.welcome_email(self).deliver_later
  end
end
```

```ruby
user = User.create(name: 'John', email: 'john@example.com')
# 保存後、自動的にウェルカムメールが送信される
```

### before_destroy の例

```ruby
class User < ApplicationRecord
  before_destroy :check_admin

  private

  def check_admin
    if admin?
      errors.add(:base, 'Cannot delete admin user')
      throw(:abort)  # 削除をキャンセル
    end
  end
end
```

```ruby
admin = User.find_by(admin: true)
admin.destroy  # => false（削除されない）
admin.errors.full_messages  # => ["Cannot delete admin user"]
```

## コールバックのスキップ

### バリデーションをスキップ

```ruby
user.save(validate: false)  # バリデーションなしで保存
```

### コールバックをスキップ

以下のメソッドはコールバックをスキップします：

```ruby
user.update_column(:name, 'John')  # コールバック・バリデーションなし
user.update_columns(name: 'John', email: 'john@example.com')
user.delete  # destroy と異なり、コールバックなし
User.update_all(active: true)  # 全レコード更新、コールバックなし
User.delete_all  # 全レコード削除、コールバックなし
```

注意: これらのメソッドは、パフォーマンスが重要な場合や、コールバックを意図的に回避したい場合にのみ使用してください。

## トランザクションコールバック

### after_commit / after_rollback

データベーストランザクションのコミット/ロールバック後に実行されます。

```ruby
class User < ApplicationRecord
  after_commit :log_user_saved, on: :create
  after_commit :log_user_updated, on: :update
  after_commit :log_user_destroyed, on: :destroy

  after_rollback :log_rollback

  private

  def log_user_saved
    Rails.logger.info "User #{id} was saved"
  end

  def log_rollback
    Rails.logger.warn "Transaction was rolled back"
  end
end
```

### 用途

- ファイルのアップロード（トランザクション成功後に実行）
- 外部APIへの通知
- キャッシュのクリア
- ジョブのエンキュー

## 条件付きコールバック

### if / unless

```ruby
class User < ApplicationRecord
  before_save :encrypt_password, if: :password_changed?
  after_create :send_welcome_email, unless: :admin?

  private

  def encrypt_password
    self.encrypted_password = BCrypt::Password.create(password)
  end

  def send_welcome_email
    UserMailer.welcome_email(self).deliver_later
  end
end
```

### Procを使った条件

```ruby
class User < ApplicationRecord
  before_save :normalize_email, if: -> { email.present? && email_changed? }
end
```

## コールバックの中止

### throw(:abort)

コールバック内で `throw(:abort)` を呼ぶと、処理がキャンセルされます。

```ruby
class User < ApplicationRecord
  before_save :check_permission

  private

  def check_permission
    unless has_permission?
      throw(:abort)  # 保存をキャンセル
    end
  end
end
```

```ruby
user.save  # => false（保存されない）
```

## バリデーションとコールバックの順序

### 完全な順序

```
1. before_validation
2. バリデーション実行
3. after_validation
4. before_save
5. before_create (or before_update)
6. データベース保存
7. after_create (or after_update)
8. after_save
9. after_commit (or after_rollback)
```

## ベストプラクティス

### バリデーション

- モデルの責務に関するバリデーションのみ定義
- カスタムバリデーションは `validate` メソッドを使用
- エラーメッセージはロケールファイルで管理

### コールバック

- コールバックは最小限に
- 複雑なロジックはサービスオブジェクトに移動
- 外部API呼び出しは `after_commit` で行う
- パフォーマンスに注意（N+1問題など）

### 例: シンプルに保つ

```ruby
# 良い例
class User < ApplicationRecord
  validates :email, presence: true
  before_save :normalize_email

  private

  def normalize_email
    self.email = email.downcase
  end
end

# 悪い例（複雑すぎる）
class User < ApplicationRecord
  before_save :do_everything

  private

  def do_everything
    normalize_email
    geocode_address
    send_notification
    update_cache
    call_external_api
  end
end
```

## まとめ

- バリデーションは `save`, `create`, `update` 時に自動実行
- エラーは `errors` オブジェクトに格納
- コールバックはオブジェクトのライフサイクルで自動実行
- `before_save`, `after_create` などで処理を挿入
- `throw(:abort)` で処理をキャンセル
- `after_commit` でトランザクション確定後の処理を実行
- 規約に従うことで、データの整合性が自動的に保たれる
