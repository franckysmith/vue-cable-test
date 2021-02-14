<template>
  <div>
    <ul>
      <li v-for="affaire in affaires" :key="affaire.id">
        <p>{{ affaire.name }}</p>
      </li>
    </ul>
    <div class="entete">
      <input
        v-model="affaire"
        class="titre_affaire"
        type="text"
        placeholder="Nom de l'affaire"
      />
      <input class="button" type="submit" value="Résumé" />
    </div>
    <div class="dates">
      <label for="prepa">Prépa</label>
      <input style="width:140px" type="date" name="retour" />
      <label for="r_am">matin</label>
      <input type="checkbox" name="r_am" />
      <label for="r_pm">aprèsMidi</label>
      <input type="checkbox" name="r_pm" />
    </div>
    <div>
      <div class="dates">
        <label for="sortie">Sortie</label>
        <input style="width:140px" type="date" name="sortie" />
        <label for="s_matin">matin</label>
        <input type="checkbox" name="s_matin" />
        <label for="s_apm">aprèsMidi</label>
        <input type="checkbox" name="s_apm" />
      </div>
      <div class="dates">
        <label for="retour">Retour</label>
        <input style="width:140px" type="date" name="retour" />
        <label for="r_am">matin</label>
        <input type="checkbox" name="r_am" />
        <label for="r_pm">aprèsMidi</label>
        <input type="checkbox" name="r_pm" />
      </div>

      <div class="cont_2">
        <div class="tech">
          <input type="text" placeholder="Nom du technicien" />

          <label for="face">face</label>
          <input type="checkbox" name="face" />
          <label for="mon"> mon</label>
          <input type="checkbox" name="mon" />
          <label for="scene"> scène</label>
          <input type="checkbox" name="scene" />
        </div>
        <div>
          <div>
            <label for="s_apm">Terminé </label>
            <input type="checkbox" name="s_apm" />
          </div>
          <div>
            <label for="update">Update </label>
            <input style="width:100px" type="date" name="update" />
          </div>
        </div>
      </div>
    </div>
    <div class="list">
      <div>
        <div v-for="cablage in cablages" :key="cablage.id">
          <ul class="list_affaire">
            <li>
              <p>{{ cablage.affaire }}</p>
            </li>
            <li>
              <p>{{ cablage.date_sortie }}</p>
            </li>

            <!-- <li>{{ cablage.face }}</li>
            <li>{{ cablage.mon }}</li>
            <li>{{ cablage.scene }}</li> -->
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// import util from "../js/lib/util.js";
// import url from "../js/lib/url.js";
// import ajax from "../js/lib/ajax.js";
import api from "../js/api.js";

//import axios from "axios";
// import ref from "vue";

export default {
  data() {
    return {
      cablages: [],
      affaires: []
    };
  },

  methods() {
    var searchby = {
      //tech_id: 3,
      name: "Casino de Paris"
    };

    api
      .call("affair_get", searchby)
      .then(res => {
        this.affaires = res.data;
        console.log("Affaires:res:", res);
      })
      .catch(error => {
        console.log("typ", error);
      });
    // .then(function(response) {
    //   console.log("affair_get:");
    //   console.log(response);
    // })
    // .catch(function(response) {
    //   console.log("affair_get:");
    //   console.log(response);
    // });
  }
};
</script>

<style scoped>
input {
  padding: 5px;
}
.entete {
  display: flex;
  justify-content: space-around;
}
.titre_affaire {
  width: 200px;
  height: 32px;
  left: 34px;
  top: 63px;

  background: linear-gradient(180deg, #ffffff 0%, rgba(255, 255, 255, 0) 100%);
  border: 1px solid #000000;
  box-sizing: border-box;
  box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
}
.button {
  margin: 10px;
  padding: 5px;
  min-width: 50px;
  background: #4dcc59;
  border: 1px solid #000000;
  box-sizing: border-box;
  box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
  border-radius: 4px;
}
.dates {
  margin: 10px;
  display: flex;
  justify-content: space-around;
}
.cont_2 {
  display: flex;
}
.cont_2 input {
  margin: 5px;
}
.tech {
  width: 200px;
}
.poste {
  margin: 10px 0px 20px 0px;
}
.list {
  margin: 35px 10px;
  display: flex;
}
.list_affaire {
  display: flex;
  justify-content: space-between;
  text-align: left;
  margin: 10px;
}
.list_affaire li {
  margin: 0px 20px;
}
.list input {
  margin: 0px 10px;
  width: 30px;
}
.list_name {
  width: 220px;
}
</style>
