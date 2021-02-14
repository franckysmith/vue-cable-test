<template>
  <div>
    <p>Modèle</p>
    <ul>
      <li v-for="enceinte in enceintes" :key="enceinte.id">
        <div class="container">
          <div>{{ enceinte.id }}</div>
          <div>{{ enceinte.typ }}</div>
          <div>{{ enceinte.poids }}</div>
        </div>
      </li>
      <div>
        <input type="text" id="poids" />
        <button type="submit" @click="submitData(poids)">click</button>
      </div>
      <li v-for="cablage in cablages" :key="cablage.id">
        <p>{{ cablage.affaire }}</p>
      </li>
    </ul>
  </div>
</template>

<script>
// import ajax from "../js/lib/ajax.js";
import axios from "axios";

export default {
  data() {
    return {
      name: "",
      poids: "",
      email: "",
      telephone: "",
      enceintes: [],
      cablages: []
    };
  },
  created() {
    axios
      .get("http://vue3/src/controllers/getData.php")
      .then(res => {
        this.enceintes = res.data;
        console.log("Enceintes:res:", res);
      })
      .catch(error => {
        console.log("typ", error);
      });
  },
  methods: {
    submitData() {
      axios
        .post("http://vue3/src/controllers/postData.php")
        .then(res => (this.poids = res.enceinte.poids))
        .catch(err => console.error(err));
    }
  }
};
</script>

<style scoped>
.about {
  display: flex;
  /* justify-content: center; */
  flex-direction: column;
  width: 150px;
}
.container {
  display: flex;
  justify-content: space-around;
}
input {
  margin-top: 10px;
}
</style>
