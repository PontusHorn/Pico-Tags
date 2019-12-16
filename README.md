# Pico Tags

A plugin for the flat file CMS [Pico](https://github.com/picocms/Pico). Using this plugin, you can use the `Tags` and
`Filter` headers in the page meta block in order to modify the `pages` array for pages of your choice. This creates the
possibility to feature index pages which show only posts of a certain type.

The `Tags` header accepts a comma-separated list of tags that apply to the current page.

The `Filter` header also accepts a comma-separated list of tags, but instead specifies which pages end up in the `pages`
array. A page with no `Filter` header will have an unfiltered list of pages, whereas a page that specifies the header
`Filter: foo, bar` will receive in its `pages` array only pages having at least one of those two tags.

## Installation

Copy the file `PicoTags.php` to the `plugins` subdirectory of your Pico installation directory. That's it!

## Usage

### Basic usage

To assign tags to a page, specify the `Tags` header inside the meta header block at the top of your file, e.g.:

#### In blog/my-first-blog-post.md
```
---
Title: My first blog post
Date: 2015-09-16 13:37:00
Description: A thrilling, must-read blog post about pancakes.
Tags: blog, pancakes
Template: blog-post
---
```

To only show pages with certain tags on another page, use the `Filter` header, e.g.:

#### In blog/index.md
```
---
Title: Blog
Filter: blog
Template: blog-list
---

These are all my blog posts:
```

Actually looping through the filtered list of pages (in the above case, pages tagged `blog`) to display them would be
done in the template file, e.g.:

#### In themes/default/blog-list.html
```twig
{{ content }}
{% for page in pages if page.title %}
    <article>
        <h2><a href="{{ page.url }}">{{ page.title }}</a></h2>
        <p>{{ page.description }}</p>
    </article>
{% endfor %}
```

### Listing related documents

You can list documents with the same tags as the current page by including something like this in your template:

```twig
<aside> 
{% for tag in meta.tags %}
    <h3>More pages tagged: <a href="{{ base_url }}/tags/?tag={{ tag }}">{{ tag }}</a></h3>
    <ul>
    {% for page in pages if page.title and page.meta.tags %}
        {% if tag in page.meta.tags and not (page.id ends with 'index') and page.id != current_page.id %}
        <li>
            <a href="{{ page.url }}">{{ page.title }}</a> - {{ page.description }}
        </li>
        {% endif %}
    {% endfor %}
    </ul>
{% endfor %}
</aside> 
```

### Listing all tags used on the site

The plugin also makes the function `get_all_tags()` available in your templates. This example uses it to create a dedicated tags page for either showing a list of available tags, or a list of documents with a specific tag:

#### In tags.md
```
---
Title: Tags
Template: tags
---

Content can go here

```

#### In themes/default/tags.html
```twig
{% extends "index.twig" %}

{% set tag = url_param('tag', 'string') %}
{% set tags = get_all_tags() %}

{% block content %}
{{ parent() }}
{% if tag %}
    <ul>
    {% for page in pages if page.title and tags and not (page.id ends with 'index') %}
        {% set pageTags = page.meta.tags|split(',') %}
        {% if tag in pageTags %}
            <li><a href="{{ page.url }}">
            {{ page.title }} - {{ page.meta.tags }}</a>
            </li>
        {% endif %}
    {% endfor %}
    </ul>
{% else %}
    <ul>
    {% for tag in tags %}
        <li><a href="{{current_page.url}}/?tag={{ tag }}">{{ tag }}</li>
    {% endfor %}
    </ul>
{% endif %}
{% endblock content %}
```

This would make e.g. `https://www.example.com/tags?tag=pancakes` show a list of documents
with the tag `pancakes`, whereas `https://www.example.com/tags` would show a list of all available tags.
