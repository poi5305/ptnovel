function Client() {
    var client = "client/";
    var args = Array.prototype.slice.call(arguments);
    this.get = function(cb) {
        var query = client + args.join("/");
        console.log(query);
        $.get(query).done(cb);
        return this;
    }
    this.url = function() {
        var query = client + args.join("/");
        return query;
    }
    return this;
}

// test client
//var c = new Client("getBookList").get(function(r) {
//    console.log(r);
//});