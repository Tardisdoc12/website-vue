<template>
  <div class="accordion">
    <div :style="AccordionHeader" @click="toggle">
      <div :style="AccordionTitle">{{ title }}</div>
      <span :style="AccordionArrow">{{ isOpen ? "▲" : "▼" }}</span>
    
    </div>

    <transition name="slide-fade">
      <div v-if="isOpen">
        <slot></slot>
      </div>
    </transition>
  </div>
</template>

<script>

export default {
  props: {
    title: {
      required: true,
      type: String
    },
    backgroundColor: {
      required: false,
      type: String
    },
    backgroundColorOpen: {
        required: false,
        type: String
    },
  },
  data() {
    return {
      isOpen: false,
    }
  },

  computed: {
    AccordionHeader() {
      let color = this.backgroundColor ? this.backgroundColor : "#2d5c7f"  
      if (this.isOpen) {
        color = this.backgroundColorOpen ? this.backgroundColorOpen : "#FFFFFF"
      }
      return {
        "background-color": color,
        "border": "1px solid " + (this.backgroundColor ? this.backgroundColor : "#2d5c7f"),
        "color": "white",
        "padding": "10px 15px",
        "cursor": "pointer",
        "font-weight": "bold",
        "display": "flex",
        "align-items": "center",
        "position": "relative",
        "height": "50px",
      }
    },
    AccordionTitle() {
        let color = this.backgroundColorOpen ? this.backgroundColorOpen : "#FFFFFF"  
        if (this.isOpen) {
            color = this.backgroundColor ? this.backgroundColor : "#2d5c7f"
        }
        if ( this.backgroundColor && this.backgroundColorOpen && this.backgroundColor === this.backgroundColorOpen) {
            color = "white"
        }
        return {
            "flex": 1,
            "text-align": "center",
            "color": color,
        }
    },
    AccordionArrow() {
        let color = this.backgroundColorOpen ? this.backgroundColorOpen : "#FFFFFF"  
        if (this.isOpen) {
            color = this.backgroundColor ? this.backgroundColor : "#2d5c7f"
        }
        if ( this.backgroundColor && this.backgroundColorOpen && this.backgroundColor === this.backgroundColorOpen) {
            color = "white"
        }
        return {
            "position": "absolute",
            "left": "15px",
            "color":color,
        }
    }
  },

  methods: {
    toggle() {
      this.isOpen = !this.isOpen
    }   
  }
}

</script>

<style scoped>
.accordion {
  width: 80%;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: white;
  overflow: hidden;
  font-family: Arial, sans-serif;
}

/* Centrer le titre */
.accordion-title {
  flex: 1;
  text-align: center;
}

/* Placer la flèche à droite */
.accordion-arrow {
  position: absolute;
  left: 15px;
}

.accordion-header:hover {
  background-color: #3b6c91;
}

.accordion-content {
  background-color: #f8f9fa;
}

/* Animation douce pour l'ouverture */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-5px);
}
</style>
