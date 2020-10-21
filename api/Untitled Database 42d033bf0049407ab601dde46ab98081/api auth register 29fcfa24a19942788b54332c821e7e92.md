# /api/auth/register

参数: name,email,password,password_confirmation
失败响应: {
"code": 400,
"message": "xxxxxxxxxx",
"success": false
}
成功响应: {
"code": 200,
"success": true,
"message": "XXX:An email will be sent to your email address,Please Check!"
}
方法: POST
登录: 否