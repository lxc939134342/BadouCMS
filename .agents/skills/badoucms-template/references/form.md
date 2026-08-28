# 留言、评论和自定义表单

原始来源：`form.md`、`message.md`、`comment.md`。

留言表单 action 为 `$bd.msgaction`，自定义表单 action 由 `form` 标签生成；两者必须 POST，字段 `name` 必须与后台表单字段一致。验证码地址为 `$bd.checkcode`。`form` 与 `formlist` 都需要 `fcode`；`formlist` 可设置 `num`、`page`，字段为 `n`、`i`、`date`、`xxx`（自定义字段）。

`message` 可全站调用，参数 `num`、`page`，字段为 `n`、`i`、`contacts`、`mobile`、`content`、`recontent`、`ip`、`os`、`bs`、`askdate`、`replydate`、`nickname`、`username`、`headpic` 和 `message.***`。

`comment` 仅放内容详情页，`contentid` 必填，可设置 `num`、`page`；在其内部嵌套 `commentsub`。评论显示前按 `$bd.commentstatus` 判断，验证码按 `$bd.commentcodestatus` 判断；提交地址是 `$bd.commentaction`。评论字段含 `comment`、`date`、`nickname`、`headpic`、`pnickname`、`replyaction` 等。
