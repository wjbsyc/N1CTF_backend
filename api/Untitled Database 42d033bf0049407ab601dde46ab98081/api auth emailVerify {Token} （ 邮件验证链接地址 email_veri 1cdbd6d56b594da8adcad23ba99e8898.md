# /api/auth/emailVerify/{Token}  （ 邮件验证链接地址: /email_verify?code=TOKEN）

参数: 无-   header:  Authorization: Bearer XXXXX(access_token)
失败响应: {
"code": 400,
"message": "XXXXXX!",
"success": false
}
成功响应: {
"code": 200,
"success": true,
"message": "You have successfully verified your email address."
}
方法: GET
登录: 是