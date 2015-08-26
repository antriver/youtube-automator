(function() {
    function pad(number) {
        if (number < 10) {
            return '0' + number;
        }
        return number;
    }

    if (!Date.prototype.toDateTime) {
        Date.prototype.toDateTime = function() {
            return this.getFullYear()
                + '-' + pad(this.getMonth() + 1)
                + '-' + pad(this.getDate())
                + ' ' + pad(this.getHours())
                + ':' + pad(this.getMinutes());
        };
    }

    if (!Date.prototype.toYMD) {
        Date.prototype.toYMD = function() {
            return String(this.getFullYear()) + '-' + pad(this.getMonth() + 1) + '-' + pad(this.getDate());
        };
    }

    if (!Date.prototype.toHM) {
        Date.prototype.toHM = function() {
            return String(pad(this.getHours()) + ':' + pad(this.getMinutes()));
        };
    }

})();
