<template>
  <div>
    <ul>
      <li v-for="cable in cables" :key="cable.cableid">
        <p>{{ cable.name }}</p>
      </li>
    </ul>
    <form @submit.prevent="addAffaire">
      <div class="entete">
        <input
          v-model="name"
          class="titre_affaire"
          type="text"
          placeholder="Nom de l'affaire"
        />
        <input class="button" type="submit" value="Ma liste" />
        <input class="button" type="submit" value="New" />
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
            <input class="button" type="submit" value="Résumé" />
            <input class="button" type="submit" value="Remarques" />
          </div>
          <div style="display:flex">
            <div>
              <input
                @click="submit"
                class="button"
                type="submit"
                value="Enregistrer"
              />

              <label for="end">Terminé </label>
              <input type="checkbox" name="end" id="end" />
            </div>

            <div>
              <label for="update">Update </label>
              <input style="width:100px" type="date" name="update" />
            </div>
          </div>
        </div>
        <div class="poste">
          <input class="button" type="submit" value="HP" />
          <input class="button" type="submit" value="Module" />
          <input class="button" type="submit" value="Elec" />
          <input class="button" type="submit" value="Spécial" />
          <input class="button" type="submit" value="Micros" />
        </div>
      </div>

      <div class="list">
        <input style="display:none" type="checkbox" name="ok" />
        <h5 class="list_name"></h5>
        <input style="border:0px" type="text" placeholder="nb" />
        <input style="border:0px" type="number" placeholder="secu" />
        <input style="border:0px" type="number" placeholder="dispo" />
      </div>
      <div class="list">
        <input type="checkbox" name="ok" />
        <h5 class="list_name">DO.7</h5>
        <input type="int" name="nb" />
        <input type="number" name="secu" />
        <input type="number" name="dispo" />
      </div>
      <div v-for="cable in cables" :key="cable.cableid"></div>
    </form>
    <br />
  </div>
</template>

<script>
// import api from "../js/api.js";
import axios from "axios";

export default {
  data() {
    return {
      cables: [],
      cable: [],
      name: "",
      prepa: "",
      sortie: "",
      retour: "",
      p_am: false,
      p_pm: false,
      s_am: false,
      s_pm: false,
      r_am: false,
      r_pm: false,
      technicien: "",
      termine: false,
      face: false,
      mon: false,
      scene: false,
      create: "",
      update: "",
      do07: "",
      do7: "",
      do10: "",
      do20: ""
    };
  },

  mounted() {
    axios
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
  margin: 5px 25px;
  display: flex;
  text-align: left;
}

.list input {
  margin: 0px 10px;
  width: 30px;
}
.list_name {
  width: 220px;
}
</style>
