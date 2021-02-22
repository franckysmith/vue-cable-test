<template>
  <div class="list_container">
    <h1>Page Master</h1>
    <div>
      <form @submit="updateCable">
        <div class="number" v-for="cable in cableSelected" :key="cable.cableid">
          <div>
            <input id="id1" v-model="cable.name" name="name" />
            <input
              id="id2"
              v-model="cable.type"
              placeholder="electrique, mmodule"
              name="name"
            />
          </div>

          <div>
            <p>secu</p>
            <input v-model="cable.reserved" name="secu" />
          </div>
          <div>
            <p>total</p>
            <input v-model="cable.total" name="total" />
          </div>
          <div>
            <textarea v-model="cable.info"></textarea>
            <input id="id3" v-model="cable.link" type="text" />
          </div>
          <div>
            <div>
              <button type="submit">
                Update
              </button>
            </div>
            <div><button>nouveau</button></div>
          </div>
        </div>
      </form>
    </div>

    <div class="head">
      <div>secu</div>
      <div style="padding-left:20px">total</div>
      <div style="padding-left:12px">réservé</div>
    </div>
    <div class="number" v-for="(cable, id) in cables" :key="id">
      <div>
        <p id="name">{{ cable.name }}</p>
      </div>

      <div><input v-model="cable.reserved" name="secu" /></div>
      <div><input v-model="cable.total" name="total" /></div>
      <div><input v-model="cable.reserved" name="reserved" /></div>
      <div><button @click="open_info" name="info">info</button></div>
      <div><button id="id" :href="cable.link">link</button></div>
      <div>
        <button @click="edit_cable(cable.cableid)" name="edit">edit</button>
      </div>
      <div><button @click="delete_cable(id)" name="delete">delete</button></div>
    </div>
  </div>
</template>

<script>
import { Api } from "../js/api.js";
var url = "https://cinod.fr/cables/api.php";

var api = new Api(url);
export default {
  data() {
    return {
      cables: [],
      cableid: "",
      info: "",
      link: "",
      name: "",
      ordered: "",
      reserved: "",
      timestamp: "",
      total: "",
      type: "",

      cableSelected: []
    };
  },
  methods: {
    edit_cable(param) {
      this.cableSelected = this.cables.filter(cable => {
        return cable.cableid == param;
      });
    }
  },
  setup() {
    function updateCable(truc) {
      console.log("truc", truc);
      let data = [
        {
          cableid: truc, // put here real cableid that were added via 'cable_add', see it in 'cable' table in phpmyadmin
          name: this.name,
          type: this.type,
          total: this.total
        }
      ];

      api
        .call("cable_update", data)
        .then(response => {
          this.cables = response;
          console.log("cable_update:", response);
        })
        .catch(response => {
          console.log("cable_update:");
          console.log(response);
        });
    }
    return {
      updateCable
    };
  },

  mounted() {
    api
      .call("cable_get")
      .then(response => {
        console.log("cable_get:", response);
        this.cables = response;
      })
      .catch(response => {
        console.log("cable_get:", response);
      });
  }
};
</script>

<style scoped>
.head {
  display: flex;
  margin-left: 200px;

  text-align: left;
}
.number {
  display: flex;
  border-width: 0px 0px 1px 0px;
  border-style: solid;
}
.number input {
  width: 30px;
  margin: 10px;
}
.number button {
  line-height: 10px;
  padding: 4px;
  margin-top: 10px;
  margin-right: 4px;
}
.list_container {
  width: 400px;
}
button {
  margin: 3px;
}
#name {
  text-align: left;
  width: 180px;
  margin-left: 20px;
  line-height: 40px;
}
#id1 {
  margin: 5px 8px;
  height: 30px;
  width: 150px;
}
#id2 {
  margin: 5px 8px;
  height: 20px;
  width: 150px;
}
#id3 {
  margin: 5px 8px;
  height: 20px;
  width: 160px;
}
#id4 {
  margin: 10px 8px;
  height: 20px;
  padding: 5px;
}
</style>
