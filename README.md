# Blade Storybook

A storybook-style preview interface for class-based Blade components, driven by PHP attributes.

## Installation

```bash
composer require itsrd/blade-storybook
```

Publish the config:

```bash
php artisan vendor:publish --tag="blade-storybook-config"
```

The storybook is enabled on `local` by default and lives at `/storybook`.

## Usage

Add the `#[Storybook]` attribute to a class-based Blade component. Components without it are ignored.

```php
use ItsRD\BladeStorybook\Attributes\Prop;
use ItsRD\BladeStorybook\Attributes\Slot;
use ItsRD\BladeStorybook\Attributes\Story;
use ItsRD\BladeStorybook\Attributes\Storybook;

#[Storybook(category: 'Forms', name: 'Button', description: 'The primary action button.')]
#[Story('Primary', ['styleType' => 'primary'])]
#[Story('Disabled', ['disabled' => true], ['default' => 'Not available'])]
#[Slot('default', default: 'Save')]
final class ButtonComponent extends Component
{
    public function __construct(
        #[Prop(options: ['small', 'large'], description: 'Padding and text size')]
        public string $size = 'small',
        public bool $disabled = false,
    ) {}
}
```

### Attributes

| Attribute | Target | What it does |
| --- | --- | --- |
| `#[Storybook]` | class | Adds the component to the storybook under a category. |
| `#[Story]` | class, repeatable | A named example with fixed props and slots. |
| `#[Slot]` | class, repeatable | An editable slot with a starting value. Plain text only. |
| `#[Prop]` | parameter | Optional extra info: `options` renders a dropdown, `description` shows help text. Name, type and default come from reflection. |

## Configuration

Everything lives in `config/blade-storybook.php`:

- `enabled` – whether the routes are registered. Defaults to `local`, override with `BLADE_STORYBOOK_ENABLED`.
- `route_prefix` – where the storybook is served.
- `middleware` – the full stack for every route. Add auth here when exposing it beyond local.
- `paths` – directories scanned for components.
- `preview.assets` – Vite entry points loaded in the preview iframe, so components look like they do in the app.
- `preview.body_class` – classes applied to the preview body.
- `viewports` – widths in pixels for the viewport buttons. `null` means full width.

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE.md](LICENSE.md).
