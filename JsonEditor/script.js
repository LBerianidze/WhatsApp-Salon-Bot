$(function ()
{

    $.ajax(
        {
            type: "GET",
            url: "GetJsonKeys.php",
            success: function (result)
            {
               var json= JSON.parse(result);
                var select = document.getElementById("jsonKeys");
              for (var i = 0;i<json.length;i++)
              {
                  select.options[i] = new Option(json[i], json[i]);
              }
              select.onchange();

            },
            error: function (error)
            {
            }
        }
    )
})
String.prototype.replaceAll = function (search, replacement)
{
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};
function loadItemByKey()
{
    var values = {
        "Param": document.getElementById('jsonKeys')
            .options[document.getElementById('jsonKeys').selectedIndex].getAttribute('value')
    };
    $.ajax(
        {
            type: "GET",
            url: "GetJsonParameter.php",
            data: values,
            success: function (result)
            {
                document.getElementById('jsoneditor').value = result.replaceAll("<br>", "\r\n")
            },
            error: function (error)
            {
            }
        }
    )
}
function Save()
{
    var values = {
        "Param":document.getElementById('jsonKeys')
            .options[document.getElementById('jsonKeys').selectedIndex].getAttribute('value'),
        "Value":document.getElementById('jsoneditor').value,
    };
    $.ajax(
        {
            type: "POST",
            url: "SaveJsonParameter.php",
            data: values,
            success: function (result)
            {
                if(result==1)
                {
                    alert("Сохранено");
                }
                else
                {
                    alert("Функия отключена разработчиком!!!");
                }
            },
            error: function (error)
            {
            }
        }
    )
}