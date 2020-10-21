# /api/auth/resetPass    (发送重置密码邮件）

参数: email
失败响应: {
"success": false,
"message": "xxxxxxxxxxxx",
"code": 400
}
成功响应: {
"code": 200,
"success": true,
"message": "A reset email has been sent! Please check your email."
}
方法: POST
登录: 否