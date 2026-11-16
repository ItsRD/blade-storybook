# Blade Storybook

PACKAGE IS STILL IN DEVELOPMENT AND NOT PUBLISHED JUST YET

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
| `#[Slot]` | class, repeatable | An editable slot with a starting value. Slot content from `#[Slot]` and `#[Story]` is compiled as Blade, so nested components like `<x-button>` render; text edited in the controls is shown as plain text. |
| `#[Prop]` | parameter | Optional extra info: `options` renders a dropdown, `description` shows help text. Name, type and default come from reflection. |

### Container width

Components that size themselves to their parent can be previewed inside a box of a set width. Set a default on the component, override it per story, and change it live with the **Container width** field in the sidebar. Bare numbers are pixels; `px`, `%`, `rem`, `em`, `vw` and `ch` are accepted. An empty field means full width.

```php
#[Storybook(category: 'Profiles', width: '320px')]
#[Story('Wide', ['user' => ...], width: '50%')]
```

### Objects, DTOs and models

Props typed as an object get no control, but a story can supply them. Simple DTOs can be built inline, since PHP allows `new` in attributes:

```php
#[Story('Guest', ['user' => new UserData(name: 'Guest', role: Role::Guest)])]
```

Anything that needs real code, such as factories, guarded models or DTOs built from models, goes in a static method referenced with `using`. It runs when the story is shown, and the array it returns overrides the inline props. Controls still apply on top.

```php
#[Story('Admin', using: [ProfileStories::class, 'admin'])]
final class ProfileCard extends Component { /* ... */ }

final class ProfileStories
{
    public static function admin(): array
    {
        $user = User::factory()->admin()->make(['name' => 'Jan']);
        $user->setRelation('dogs', Dog::factory()->count(2)->make());

        return ['user' => UserData::fromModel($user)];
    }
}
```

Use `make()`, not `create()`: nothing is written to the database. Factories ignore `$guarded`, and relations you set with `setRelation()` are used as is. If the method throws, the preview shows the error instead of the component.

As a safety net, every storybook page runs inside a database transaction that is always rolled back. A story that slips and writes anyway leaves no rows behind. This happens, for example, when a factory definition contains `'user_id' => User::factory()`, which `make()` still creates. Two side effects remain: auto-increment counters still go up, and anything outside the database still happens, such as queued jobs on Redis or mail sent from a model observer. So keep overriding foreign keys and setting relations. Configure it under `rollback` in the config: list every connection your stories touch.

## Configuration

Everything lives in `config/blade-storybook.php`:

- `enabled` – whether the routes are registered. Defaults to `local`, override with `BLADE_STORYBOOK_ENABLED`.
- `route_prefix` – where the storybook is served.
- `middleware` – the full stack for every route. Add auth here when exposing it beyond local.
- `paths` – directories scanned for components.
- `preview.assets` – Vite entry points loaded in the preview iframe, so components look like they do in the app.
- `preview.head` – extra markup for the preview `<head>`, for anything your layout loads outside of Vite, such as web fonts. A view name or a raw HTML string.
- `preview.scripts` – extra markup at the end of the preview `<body>`, such as a CDN build of Alpine or other scripts your layout adds. A view name or a raw HTML string.
- `preview.livewire` – when Livewire is installed, its styles and scripts (which bundle Alpine) are added to the preview, so components using `x-data` work. Set to `false` to turn this off.
- `preview.body_class` – classes applied to the preview body.
- `rollback.enabled` / `rollback.connections` – roll back every database write made while rendering the storybook. `null` is the default connection.

```php
'preview' => [
    'assets' => ['resources/css/app.css', 'resources/js/app.js'],
    'head' => '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap">',
    'scripts' => 'partials.storybook-scripts', // a view
],
```

Without Livewire, Alpine has to come from somewhere: start it in one of your `preview.assets` entries or load it through `preview.scripts`.
- `viewports` – widths in pixels for the viewport buttons. `null` means full width.

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE.md](LICENSE.md).
