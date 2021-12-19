$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
    }
});
var element = Array();
window.generateDashboard = function() {
    $.ajax({
        url: base_path+'dashboard/template',
        type: "GET",
        dataType: 'json',
        success: function (data) {
            if (data) {
                // createNewElement(document.getElementById('Dashboard'),{kind:'h1',innerhtml:'Dashboard'});
                let row = createNewElement(document.getElementById('Dashboard'),{kind:'div',class:'row'});
                Object.entries(data).forEach(([key_result, value_result]) => {
                    let col = createNewElement(row,{kind:'div',class:'col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12'});
                    let infoBox = createNewElement(col,{kind:'div',class:'info-box shadow'});
                    createNewElement(infoBox,{kind:'span',class:'info-box-icon '+value_result.background,innerhtml:'<i class="'+value_result.icon+'"></i>'});
                    let infoBoxContent = createNewElement(infoBox,{kind:'div',class:'info-box-content'});
                    createNewElement(infoBoxContent,{kind:'span',class:'info-box-text',innerhtml:value_result.label});
                    createNewElement(infoBoxContent,{kind:'span',class:'info-box-number',id:'dashboard_'+key_result});
                    element.push(key_result);
                });
                populateDashboard();
                setInterval(populateDashboard, 60000);
            }
        },
        error: function (data) {
            console.log('ERR populateDashboard');
            console.log(data);
        }
    });
}
function populateDashboard() {
    $.ajax({
        url: base_path+'dashboard',
        type: "GET",
        dataType: 'json',
        success: function (data) {
            element.forEach(Element => {
                if (data[Element]!=undefined) {
                    document.getElementById('dashboard_'+Element).innerHTML = data[Element];
                }
            });
        },
        error: function (data) {
            console.log('ERR populateDashboard');
            console.log(data);
        }
    });
}
