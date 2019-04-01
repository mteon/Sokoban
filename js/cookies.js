function setCookie(cvalue) {
    let d = new Date();
    d.setTime(d.getTime() + (3650 * 24 * 60 * 60 * 1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = "lvls=" + cvalue + ";" + expires + ";path=/";
}

function deleteCookie() {
    let d = new Date();
    d.setTime(d.getTime() + (3650 * 24 * 60 * 60 * 1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = "lvls=;" + expires + ";path=/";
}

function getCookie() {
    let name = "lvls=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function getCookieLvl(lvlNb) {
    let name = "lvls=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            let lvls = (c.substring(name.length, c.length)).split(',');
            for (let j = 0; j < lvls.length; j++) {
                if (parseInt(lvls[j]) === parseInt(lvlNb)) return true;
            }
        }
    }
    return false;
}