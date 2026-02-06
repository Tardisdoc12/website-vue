<template>
  <div
    :style="{ width: width, ...Accordion }"
  >
    <div :style="AccordionHeader" @click="toggle">
      <div :style="AccordionTitle">{{ title }}</div>
      <span :style="AccordionArrow">{{ isOpen ? "▲" : "▼" }}</span>
    
    </div>

    <transition name="slide-fade">
      <div v-if="isOpen" class="accordion-content">
        <slot></slot>
      </div>
    </transition>
  </div>
</template>

<script>

export default {
  props: {
    isOpoenForced: {
      type: Boolean,
      default: false
    },
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
    borderColor: {
        required: false,
        type: String
    },
    borderColorOpen: {
        required: false,
        type: String
    },
    width: {
      type: String,
      default: '80%'
    },
    showBorder: {
      type: Boolean,
      default: true
    },
    borderRadius: {
      type: String,
      default: '6px'
    },
    borderWindowColor: {
      type: String,
      default: '#ccc'
    },
    sizeBorder: {
      type: String,
      default: '1px'
    },
  },
  data() {
    return {
      isOpen: this.isOpoenForced,
    }
  },

  computed: {
    AccordionHeader() {
      let color = this.backgroundColor ? this.backgroundColor : "#2d5c7f"
      let borderColor = this.borderColor ? this.borderColor : "#FFFFFF"
      if (this.isOpen) {
        color = this.backgroundColorOpen ? this.backgroundColorOpen : "#FFFFFF"
        borderColor = this.borderColorOpen ? this.borderColorOpen : "#2d5c7f"
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
    },
    Accordion() {
      return {
        "border": this.showBorder ? `${this.sizeBorder} solid ${this.borderWindowColor}` : "none",
        "border-radius": this.borderRadius,
        "background-color": "white",
        "overflow": "hidden",
        "font-family": "Arial, sans-serif",
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
/* Centrer le titre */
.accordion-title {
  flex: 1;
  text-align: center;
}

.accordion-content {
  margin: 0;
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
