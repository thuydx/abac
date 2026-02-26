# ABAC (Attribute-Based Access Control)
- Hybrid authorization model
- Object-level security
- Expression-based rule engine
- Production-ready architecture
- Enterprise-grade foundation
## Overview
```
Client
  ↓
SecurityService::check()
  ↓
RBAC (PermissionGate)
  ↓ if allowed
ABAC Engine (optional)
  ↓
Decision
```

## Workflow Diagram
```
                +----------------------+
                |  SecurityService     |
                +----------+-----------+
                           |
                           v
                +----------------------+
                | Normalize Permission |
                +----------+-----------+
                           |
                           v
                +----------------------+
                |   RBAC Gate Check    |
                +----------+-----------+
                           |
                     Allowed?
                    /         \
                  No           Yes
                  |              |
              DENY            Check config('rbac.abac_enabled')
                                   |
                                   v
                       +----------------------+
                       |     ABAC Engine      |
                       +----------+-----------+
                                  |
                                  v
                        +--------------------+
                        | ConstraintRepository|
                        +--------------------+
                                  |
                                  v
                        +--------------------+
                        |   Policy Chain     |
                        +--------------------+
                                  |
                                  v
                               Final
                              Decision
```

## RBAC and ABAC
| Layer  | Responsibility                         |
| ------ |----------------------------------------|
| RBAC   | Is the User has permission for action? |
| ABAC   | Is the Action Apply to which resource  |
| DSL    | Rule logic evaluation                  |
| Scope  | Isolation boundary                     |
| Module | Domain boundary                        |

## ABAC table: 
### user_permission_constraints 
| Column          | Type   | Description   |
| --------------- | ------ | ------------- |
| uuid            | UUID   | Primary key   |
| user_uuid       | UUID   | Target user   |
| scope           | string | system / site |
| module          | string | blog / cms    |
| permission_slug | string | full slug     |
| constraints     | JSON   | DSL rules     |
example constraints:
```json
{
    "expression": "owner_uuid == user_uuid && !startsWith(category, 'admin')"
}
```

## DSL Operators Supported
| Feature          | Status |
| ---------------- | ------ |
| == != > < >= <=  | ✅     |
| in               | ✅     |
| &&               | ✅     |
| !                | ✅     |
| startsWith()     | ✅     |
| endsWith()       | ✅     |
| contains()       | ✅     |
| DateTime compare | ✅     |
| now()            | ✅     |
| AST-based        | ✅     |
| No eval          | ✅     |

### Comparison
| Operator | Example                          |
| -------- | -------------------------------- |
| ==       | owner_uuid == user_uuid          |
| !=       | status != 'archived'             |
| >        | level > 50                       |
| <        | score < 10                       |
| >=       | created_at >= '2025-01-01'       |
| <=       | expires_at <= now()              |
| in       | category in ['news','marketing'] |
### Logical
| Operator | Example        |
| -------- | -------------- |
| &&       | cond1 && cond2 |
| ||       | cond1 || cond2 |
| !        | !is_admin      |
### String Functions
| Function   | Example                        |
| ---------- | ------------------------------ |
| startsWith | startsWith(email,'admin')      |
| endsWith   | endsWith(email,'@company.com') |
| contains   | contains(category,'market')    |
### DateTime
Date handling uses DateTimeImmutable.

| Usage            | Example                    |
| ---------------- | -------------------------- |
| ISO date compare | created_at >= '2025-01-01' |
| now()            | now() < expires_at         |
## How to use
### 1. Enabling ABAC
To enable ABAC, set the following configuration in your config/rbac.php:
```php
return [
    'abac_enabled' => true,
];
``` 
### 2. Insert Constraints
Example DB records
```json
{
  "expression": "owner_uuid == user_uuid || category in ['marketing']"
}
```
### 3. Call SecurityService
```php
$security->check(
    'blog.post.update',
    $user,
    [
        'owner_uuid' => $post->author_uuid,
        'category'   => $post->category_slug,
        'created_at' => $post->created_at,
    ]
);
```
### 4. Follow
1. Normalize slug
2. RBAC gate check
3. If allowed:
   - Load constraint
   - Evaluate DSL
4. Return bool
## RBAC + ABAC Best Practices
### ✅ RBAC handles:
- Role hierarchy
- Module isolation
- Scope isolation
- Level enforcement

### ✅ ABAC handles:
- Owner check
- Category check
- Status check
- Time-based check
- Object-level control

## Some Example
### Author can update their own post
```json
{
  "expression": "owner_uuid == user_uuid"
}
```
### User can access marketing content
```json
{
  "expression": "category in ['marketing']"
}
```
### User can edit if content is not archived
```json
{
  "expression": "status != 'archived'"
}
```
### User can edit if created within 30 days
```json
{
  "expression": "created_at >= now() - 30*24*60*60"
}
``` 
### time-limited access
```json
{
  "expression": "now() <= expires_at"
}
```
### Complex rule
```json
{
  "expression": "!startsWith(category,'admin') && (owner_uuid == user_uuid || contains(tags,'marketing'))"
}
```
### Example with structured rules (if you prefer not to use expression)
```json
{
  "type": "structured",
  "priority": 100,
  "decision": "allow",
  "rules": {
    "operator": "and",
    "conditions": [
      { "field": "category", "operator": "in", "value": ["A","B"] },
      {
        "operator": "or",
        "conditions": [
          { "field": "owner_uuid", "operator": "=", "value": "user_uuid" },
          { "field": "role", "operator": "=", "value": "manager" }
        ]
      }
    ]
  }
}
```
## Cache Architecture
### Constraint Cache
#### Constraint Cache
``` 
abac:constraints:{user}:{permission}:{scope}:{module}
```
#### Redis tags
```
abac
user:{uuid}
permission:{slug}
```
#### Flow
```
Request
   ↓
Repository
   ↓
Cache::tags(...)->remember()
   ↓
Redis
```
### Decision Cache (Short TTL)
```
Cache::tags(['user:{uuid}'])->flush();
```

## How to use
in config/abac.php:
``` config('abac.cache.enabled') === true ```

### Khi user role thay đổi

BẮT BUỘC gọi:
```
app(AbacCacheManager::class)
->clearUser($userUuid);
```
### Khi permission thay đổi
```
app(AbacCacheManager::class)
->clearPermission($permissionSlug);
```
### Khi sync RBAC module

Khuyến nghị:
```
app(AbacCacheManager::class)
->clearAll();
```

## CLI Commands
### Clear cache
```
php artisan abac:cache-clear --user=UUID
php artisan abac:cache-clear --permission=post.view
php artisan abac:cache-clear --all
```
### Warm cache
```
php artisan abac:cache-warm
php artisan abac:cache-warm --user=UUID
```
