# /api/auth/login

参数: email,password
失败响应: {
"code": 401,
"success": false,
"message": "Unauthorized"
}
成功响应: {
"code": 200,
"success": true,
"access_token": "xxxxx",
"token_type": "bearer",
"expires_in": 3600
}
方法: POST
登录: 否

请求

```php
/login

```

返回

```php
{
	"CODE": 200
}
```