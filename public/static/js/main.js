layui.use(['layer', 'element'], function() {
    var layer = layui.layer, element = layui.element;
});
function logout() {
    $.post('/user/logout', {'exit': true}, function() {
        return location.href = '/';
    });
}

var btnLoad = function(a, b) {
    var element = $(a), V = element.html();
    try {
        element.html('<i class="fa fa-cog fa-spin"></i> ' + (b ? b : '保存中...'));
        element.attr('disabled', true);
        element.css('cursor', 'not-allowed');
    } catch (e) {
        element.html(V);
        element.attr('disabled', false);
        element.css('cursor', 'pointer');
    }
}

var closeBtnLoad = function(a, b) {
    var element = $(a);
    element.html(b);
    element.attr('disabled', false);
    element.css('cursor', 'pointer');
}
function picDel(id) {
    layer.confirm('您真的要删除这张图片吗？', {
        title: '删除图片',
        btn: ['删除', '取消'] //按钮
    }, function() {
        layer.load(2);
        $.post('/user/picDel', {'id': id}, function(res) {
            layer.msg(res.msg, {icon: res.code ? 1 : 2});
            if(res.code) {
                fadeRemove('.pic-' + id);
            }
            layer.closeAll('loading');
        });
    });
}
function fadeRemove(a) {
    $(a).fadeTo("slow", 0.01, function() {
        $(this).slideUp("slow", function() {
            $(this).remove();
        });
    });
}

function picBatchDel() {
    var array = [];
    $('.fa-check-circle-o').each(function() {
        array.push($(this).attr('data-id'));
    });
    if(array.length > 0) {
        return layer.confirm('您真的要删除这 '+ array.length +' 张图片吗？', {
            title: '删除图片',
            btn: ['删除', '取消'] //按钮
        }, function() {
            layer.load(2);
            $.post('/user/picBatchDel', {'array': array}, function(res) {
                if(res.code) {
                    for (var i = 0; i < array.length; i++) {
                        fadeRemove('.pic-' + array[i]);
                    }
                }
                layer.msg(res.msg, {icon: res.code ? 1 : 2});
                layer.closeAll('loading');
            });
        });
    }
    return layer.msg('没有选中项');
}

function checkBoxAll() {
    var t = $('.pic-del');
    if(t.hasClass('fa-check-circle-o')) {
        t.removeClass('fa-check-circle-o');
        t.addClass('fa-circle-thin');
        $('.checkBoxAll').html('全选');
    } else {
        t.removeClass('fa-circle-thin');
        t.addClass('fa-check-circle-o');
        $('.checkBoxAll').html('反选');
    }
}
function forgot() {
    layer.open({
        type: 1,
        //area: ['500px', '300px'],
        title: '找回密码',
        shade: 0.6,
        content: '<div class="forgot" style="padding: 15px;"><div class="input-group"><input type="email" id="get-email" class="form-control" placeholder="请输入邮箱" required><span class="input-group-btn"><button class="btn btn-default" id="getEmail" onclick="getEmail()" type="button">发送验证码</button></span> </div></div>'
    });
}
function getEmail() {
    var email = $('.forgot #get-email');
    if(email.val() != '') {
        layer.load(2);
        $.post('/login/getEmailCode', {'email': $.trim(email.val())}, function(res) {
            layer.closeAll('loading');
            layer.msg(res.msg, {icon: res.code ? 1 : 2});
            if(res.code) {
                email.attr('disabled', true);
                $('#getEmail').attr('disabled', true);
                $('.forgot').append('<div class="input-group isEmailCode"><input type="text" id="email_code" class="form-control" placeholder="请输入验证码" required><span class="input-group-btn"><button class="btn btn-default" onclick="isEmailCode()" type="button">验证</button></span> </div>');
            }
        });
    } else {
        return layer.msg('请输入邮箱');
    }
}

function isEmailCode() {
    var email_code = $.trim($('#email_code').val());
    if(email_code == "") {
        return layer.msg('请输入验证码');
    } else {
        layer.load(2);
        $.post('/login/isEmailCode', {'code': email_code}, function(res) {
            layer.closeAll('loading');
            if(res.code) {
                $('.isEmailCode').remove();
                var html = '';
                html += '<input type="password" id="reset-password" class="form-control m-b-10" placeholder="密码">';
                html += '<input type="password" id="reset-passwords" class="form-control m-b-10" placeholder="确认密码">';
                html += '<button type="button" onclick="resetPassWord()" class="btn btn-primary btn-sm btn-block">重置密码</button>';
                $('.forgot').append(html);
            }
            return layer.msg(res.msg, {icon: res.code ? 1 : 2});
        });
    }
}
function resetPassWord() {
    var password = $.trim($('#reset-password').val()), passwords = $.trim($('#reset-passwords').val());
    if(password != passwords) {
        return layer.msg('两次输入的密码不一致');
    } else {
        layer.load(2);
        $.post('/login/resetPassWord', {'password': password}, function(res) {
            layer.closeAll('loading');
            if(res.code) {
                layer.alert(res.msg, {icon: 1}, function(index) {
                    layer.close(index);
                    history.go(0);
                });
            } else {
                return layer.alert(res.msg, {icon: res.code ? 1 : 0});
            }
        });
    }
}