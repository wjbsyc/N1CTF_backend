# /api/challenge/solvers/{chall_id} (某题解答者)

参数: 无
失败响应: {
"code": 401,
"message": "Please Login!",
"success": false
}
成功响应: {
"code": 200,
"success": true,
"solvers": [
{
"name": "nu1l",
"pivot": {
"challengeid": 1,
"teamid": 3,
"created_at": "2020-07-06T12:48:07.000000Z",
"userid": 39,
"rank": 1
}
}
]
}
方法: GET
登录: 是