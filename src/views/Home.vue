<template>
  <div class="home">{{ cables }}</div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      cables: []
    };
  },

  mounted() {
    axios
      .call("../api.php/cable_get")
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
