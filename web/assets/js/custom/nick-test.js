$(document).ready(function() {

    $(".form-nick").blur(function() {
     var nick = this.value;
     var url = URL.concat('/nick_test');
     console.log(url);
     $.ajax({
         url: url,
         data: {
             nick: nick
         },
         type: 'POST',
         success: function(response) {
             debugger;
             if(response == "used"){
                 $(".form-nick").css("border", "1px solid red");
             }else{
                 $(".form-nick").css("border", "1px solid green");
             }
         }
     });
   });
});