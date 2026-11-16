<?php

declare(strict_types=1);

use ItsRD\BladeStorybook\Facades\Storybook;
use ItsRD\BladeStorybook\Rendering\ComponentState;
use ItsRD\BladeStorybook\Rendering\PreviewWidth;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureButton;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureCard;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureInput;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureProfile;
use ItsRD\BladeStorybook\Tests\Fixtures\Data\UserData;

it('shows the first component when none is selected', function (): void {
    $this->get(route('blade-storybook.index'))
        ->assertOk()
        ->assertSee('Fixture Card')
        ->assertSee('&lt;x-fixture-card&gt;', false);
});

it('names the constructor parameters that are missing the prop attribute', function (): void {
    $this->get(route('blade-storybook.index', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))
        ->assertOk()
        ->assertSee('meta, internal');
});

it('renders the selected story in the preview', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
        'story' => 'secondary-small',
    ]))
        ->assertOk()
        ->assertSee('class="secondary size-2"', false)
        ->assertSee('data-meta="cancel"', false)
        ->assertSee('data-internal="none"', false)
        ->assertSee('Annuleren');
});

it('lets controls override story values', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
        'story' => 'primary',
        'props' => ['variant' => 'secondary', 'disabled' => '1'],
        'slots' => ['default' => 'Verzenden'],
    ]))
        ->assertOk()
        ->assertSee('class="secondary size-1" data-href="none" data-meta="none" data-internal="none" disabled', false)
        ->assertSee('Verzenden');
});

it('renders components nested in story slots', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
        'story' => 'with-nested-component',
    ]))
        ->assertOk()
        ->assertSee('<div data-prefix="nested"><input></div>', false)
        ->assertDontSee('&lt;x-fixture-input', false);
});

it('leaves unedited slots out of the url so their html never travels through it', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    expect(ComponentState::resolve($component, 'with-nested-component')->query())->not->toHaveKey('slots')
        ->and(ComponentState::resolve($component, 'with-nested-component', slotInput: ['footer' => 'Edited'])->query()['slots'])
        ->toBe(['footer' => 'Edited']);
});

it('keeps slot text edited in the controls as escaped text', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
        'story' => 'with-nested-component',
        'slots' => ['footer' => '<x-fixture-input /> {{ 1 + 1 }}'],
    ]))
        ->assertOk()
        ->assertSee('&lt;x-fixture-input /&gt; {{ 1 + 1 }}', false)
        ->assertDontSee('data-prefix', false);
});

it('explains why a component cannot be previewed instead of crashing', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureCard::class),
    ]))
        ->assertOk()
        ->assertSee('publishedAt');
});

it('adds raw head markup to the preview', function (): void {
    config()->set('blade-storybook.preview.head', '<link rel="stylesheet" href="https://fonts.example/nunito.css">');

    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))
        ->assertOk()
        ->assertSee('<link rel="stylesheet" href="https://fonts.example/nunito.css">', false);
});

it('includes a head view in the preview', function (): void {
    view()->addNamespace('storybook-test', __DIR__.'/Fixtures/views');
    config()->set('blade-storybook.preview.head', 'storybook-test::head');

    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))
        ->assertOk()
        ->assertSee('<meta name="storybook-head-fixture" content="1">', false);
});

it('adds raw head and scripts markup to the preview', function (): void {
    config()->set('blade-storybook.preview.head', '<link rel="stylesheet" href="https://fonts.example/nunito.css">');
    config()->set('blade-storybook.preview.scripts', '<script src="https://cdn.example/alpine.js" defer></script>');

    $response = $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))->assertOk();

    $content = (string) $response->getContent();

    expect(strpos($content, 'fonts.example'))->toBeLessThan(strpos($content, '</head>'))
        ->and(strpos($content, 'cdn.example'))->toBeGreaterThan(strpos($content, '<body'));
});

it('renders head and scripts views in the preview', function (): void {
    view()->addNamespace('storybook-test', __DIR__.'/Fixtures/views');
    config()->set('blade-storybook.preview.head', 'storybook-test::head');
    config()->set('blade-storybook.preview.scripts', 'storybook-test::scripts');

    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))
        ->assertOk()
        ->assertSee('<meta name="storybook-head-fixture" content="1">', false)
        ->assertSee('<script data-storybook-scripts-fixture></script>', false);
});

it('adds livewire styles to the head and scripts to the body', function (): void {
    require_once __DIR__.'/Fixtures/Livewire/FrontendAssets.php';

    config()->set('blade-storybook.preview.head', '<meta name="configured-head">');

    $content = (string) $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))->assertOk()->getContent();

    $headEnd = strpos($content, '</head>');

    expect(strpos($content, 'data-livewire-styles-stub'))->toBeLessThan($headEnd)
        ->and(strpos($content, 'configured-head'))->toBeLessThan($headEnd)
        ->and(strpos($content, 'data-livewire-scripts-stub'))->toBeGreaterThan($headEnd);

    config()->set('blade-storybook.preview.livewire', false);

    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureButton::class),
    ]))->assertDontSee('data-livewire-styles-stub', false);
});

it('normalizes container widths', function (mixed $input, ?string $expected): void {
    expect(PreviewWidth::normalize($input))->toBe($expected);
})->with([
    'bare number' => ['320', '320px'],
    'int' => [320, '320px'],
    'percentage' => ['50%', '50%'],
    'rem with spaces' => [' 20 REM ', '20rem'],
    'decimal' => ['12.5vw', '12.5vw'],
    'empty' => ['', null],
    'zero' => ['0', null],
    'css injection' => ['10px; background: red', null],
    'unknown unit' => ['10pt', null],
    'null' => [null, null],
]);

it('previews a component inside a container of the chosen width', function (): void {
    $input = str_replace('\\', '.', FixtureInput::class);

    // The component default from #[Storybook], overridden by the story, overridden by the control.
    $this->get(route('blade-storybook.preview', ['component' => $input, 'story' => 'default']))
        ->assertSee('data-storybook-container style="width: 320px;', false);

    $this->get(route('blade-storybook.preview', ['component' => $input, 'story' => 'with-prefix']))
        ->assertSee('data-storybook-container style="width: 50%;', false);

    $this->get(route('blade-storybook.preview', ['component' => $input, 'story' => 'with-prefix', 'width' => '18rem']))
        ->assertSee('data-storybook-container style="width: 18rem;', false);

    // An empty control clears a width set on the story.
    $this->get(route('blade-storybook.preview', ['component' => $input, 'story' => 'with-prefix', 'width' => '']))
        ->assertDontSee('data-storybook-container', false)
        ->assertSee('data-prefix="https://"', false);

    // Without a width, the component renders as before.
    $this->get(route('blade-storybook.preview', ['component' => str_replace('\\', '.', FixtureButton::class)]))
        ->assertDontSee('data-storybook-container', false);
});

it('only puts a changed width in the url', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureInput::class));

    expect(ComponentState::resolve($component, 'with-prefix')->query())->not->toHaveKey('width')
        ->and(ComponentState::resolve($component, 'with-prefix', widthInput: '100')->query()['width'])->toBe('100px')
        ->and(ComponentState::resolve($component, 'with-prefix', widthInput: '')->query()['width'])->toBe('');
});

it('renders dto props built in a story', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => 'admin',
    ]))
        ->assertOk()
        ->assertSee('class="full" data-role="admin"', false)
        ->assertSee('Jan Utrecht users.manage');

    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => 'guest',
        'props' => ['compact' => '1'],
    ]))
        ->assertOk()
        ->assertSee('class="compact" data-role="guest"', false)
        ->assertSee('Gast no-city');
});

it('points at working stories when the selected story lacks a dto', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => 'empty',
    ]))
        ->assertOk()
        ->assertSee('does not provide values for: user')
        ->assertSee('Stories that can be previewed: Admin, Guest, From model');
});

it('builds story props from a using method with guarded models and factories', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => 'from-model',
    ]))
        ->assertOk()
        ->assertSee('class="compact" data-role="admin"', false)
        ->assertSee('Henk Leiden Bobbie,Saar');
});

it('lets controls override values from a using method', function (): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => 'from-model',
        'props' => ['compact' => '0'],
    ]))
        ->assertOk()
        ->assertSee('class="full" data-role="admin"', false);
});

it('shows why a using method failed instead of crashing', function (string $story, string $message): void {
    $this->get(route('blade-storybook.preview', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => $story,
    ]))
        ->assertOk()
        ->assertSee($message);

    $this->get(route('blade-storybook.index', [
        'component' => str_replace('\\', '.', FixtureProfile::class),
        'story' => $story,
    ]))
        ->assertOk()
        ->assertSee($message);
})->with([
    'throws' => ['broken', 'Factory exploded'],
    'wrong return type' => ['not-an-array', 'must return an array of props, string returned'],
    'unknown method' => ['missing-method', 'which does not exist'],
]);

it('keeps dto values out of the url', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureProfile::class));
    $state = ComponentState::resolve($component, 'admin', ['compact' => '1']);

    expect($state->props['user'])->toBeInstanceOf(UserData::class)
        ->and($state->query()['props'])->toBe(['compact' => '1'])
        ->and($state->controlValues())->not->toHaveKey('user')
        ->and($component->isRenderable())->toBeTrue()
        ->and(ComponentState::resolve($component, 'empty')->blockingReason())->toContain('user');

    $this->get(route('blade-storybook.index', $state->query()))->assertOk();
});

it('returns 404 for an unknown component', function (): void {
    $this->get(route('blade-storybook.preview', ['component' => 'Nope']))->assertNotFound();
});

it('serves the interface assets', function (): void {
    $this->get(route('blade-storybook.asset', 'storybook.css'))
        ->assertOk()
        ->assertHeader('content-type', 'text/css; charset=UTF-8');
});
