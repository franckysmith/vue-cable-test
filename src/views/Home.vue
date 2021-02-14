<template>
  <div class="home">{{ cables }}</div>
</template>

<script>
// import util from "../js/lib/util.js";
// import url from "../js/lib/url.js";
// import ajax from "../js/lib/ajax.js";
import api from "../js/api.js";
var apiUrl = '../api.php';

var api = new Api(apiUrl);

export default {
  data() {
    return {
      cables: []
    };
  },

  setup() {
    api
      .call("cable_get")
      .then(function(response) {
        console.log("cable_get:");
        console.log(response);
        this.cables = response;
      })
      .catch(function(response) {
        console.log("cable_get:");
        console.log(response);
      });

    // api
    //   .call("affair_get", searchby)
    //   .then(res => {
    //     this.affaires = res.data;
    //     console.log("Affaires:res:", res);
    //   })
    //   .catch(error => {
    //     console.log("typ", error);
    //   });
  }
};
</script>
