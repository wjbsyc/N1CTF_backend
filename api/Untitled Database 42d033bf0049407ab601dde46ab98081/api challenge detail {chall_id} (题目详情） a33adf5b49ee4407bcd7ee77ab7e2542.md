# /api/challenge/detail/{chall_id} (题目详情）

参数: 无
失败响应: {
"code": 401,
"message": "Please Login!",
"success": false
}
成功响应: {
"code": 200,
"success": true,
"chal": {
"title": "1",
"score": 1000,
"description": "1",
"url": "1",
"class": "pwn",
"solves": 1,"hints": [
{
"id": 1,
"content": "nu1l",
"challengeid": 1,
"created_at": "2020-07-10T05:43:39.000000Z",
"updated_at": "2020-07-10T05:43:39.000000Z"
}
]
}
}
方法: GET
登录: 是