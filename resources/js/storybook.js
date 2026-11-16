import Alpine from 'alpinejs'

window.storybook = (config) => ({
    previewUrl: config.previewUrl,
    shellUrl: config.shellUrl,
    component: config.component,
    story: config.story,
    stories: config.stories ?? {},
    props: { ...config.props },
    slots: { ...config.slots },
    storySlots: { ...(config.stories?.[config.story]?.slots ?? config.slots) },
    width: config.width ?? '',
    viewport: null,

    init() {
        this.$watch('src', () => this.syncUrl())
        this.syncUrl()
    },

    get query() {
        const params = new URLSearchParams()

        params.set('component', this.component)

        if (this.story) {
            params.set('story', this.story)
        }

        Object.entries(this.props).forEach(([name, value]) => {
            params.set(`props[${name}]`, typeof value === 'boolean' ? (value ? '1' : '0') : String(value ?? ''))
        })

        Object.entries(this.slots).forEach(([name, value]) => {
            if (value === this.storySlots[name]) {
                return
            }

            params.set(`slots[${name}]`, String(value ?? ''))
        })

        params.set('width', String(this.width ?? '').trim())

        return params
    },

    get src() {
        return `${this.previewUrl}?${this.query.toString()}`
    },

    selectStory(id) {
        this.story = id

        const story = this.stories[id]

        if (!story) {
            return
        }

        this.props = { ...story.props }
        this.slots = { ...story.slots }
        this.storySlots = { ...story.slots }
        this.width = story.width ?? ''
    },

    syncUrl() {
        window.history.replaceState({}, '', `${this.shellUrl}?${this.query.toString()}`)
    },
})

window.Alpine = Alpine

Alpine.start()
