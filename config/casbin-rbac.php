<?php
return [
    // RBAC 模型配置（核心规则）
    'model' => [
        'config_type' => 'text',
        'config_text' => <<<EOF
[request_definition]
r = sub, obj, act, tenant_id

[policy_definition]
p = sub, obj, act, tenant_id

[role_definition]
g = _, _, tenant_id

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub, r.tenant_id) && r.obj == p.obj && r.act == p.act && r.tenant_id == p.tenant_id
EOF,
    ],

    // 适配器配置（仅保留策略表名）
    'adapter' => [
        'table_name' => 'casbin_rule', // 对应数据库策略表
    ],

    // 缓存配置（复用 App('cache')）
    'cache' => [
        'enable' => true,
        'ttl' => 3600,
        'key_prefix' => 'casbin_rule_',
    ]
];
