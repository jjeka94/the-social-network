function ajaxRequest() {
    try {
        var request = new XMLHttpRequest();//objekat ove klase
    }
    catch(e1) {
        try {
            request =  new ActiveXObject("Msxm12.XMLHTTP");//a-s
        }
        catch(e2) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch(e3) {
                request = false;
            }
        }
    }

    return request;
}