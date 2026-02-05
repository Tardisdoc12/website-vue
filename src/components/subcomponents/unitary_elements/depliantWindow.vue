<template>
  <div
    class="accordion"
    :style="{ width: width }"
  >
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
    writenColor: {
        required: false,
        type: String
    },
    writenColorOpen: {
        required: false,
        type: String
    },
    width: {
      type: String,
      default: '80%'
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
      let borderColor = this.writenColor ? this.writenColor : "#FFFFFF"
      if (this.isOpen) {
        color = this.backgroundColorOpen ? this.backgroundColorOpen : "#FFFFFF"
        borderColor = this.writenColorOpen ? this.writenColorOpen : "#2d5c7f"
      }
      return {
        "background-color": color,
        "border": "1px solid " + (borderColor),
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

        if (this.writenColorOpen) {
            color = this.writenColorOpen
        }

        if (this.writenColor && this.isOpen) {
            color = this.writenColor
        }

        if ( this.backgroundColor && this.backgroundColorOpen && this.backgroundColor === this.backgroundColorOpen && !this?.writenColor && !this?.writenColorOpen) {
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

        if (this.writenColorOpen) {
            color = this.writenColorOpen
        }

        if (this.writenColor && this.isOpen) {
            color = this.writenColor
        }

        if ( this.backgroundColor && this.backgroundColorOpen && this.backgroundColor === this.backgroundColorOpen && !this?.writenColor && !this?.writenColorOpen) {
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
