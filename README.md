# Pico Tags

A plugin for the flat file CMS [Pico](https://github.com/picocms/Pico). Using this plugin, you can use the `Tags` and
`Filter` headers in the page meta block in order to generate lists of pages matching specific tags. This creates the
possibility to build index pages which show only posts of a certain type.

The `Tags` header accepts a comma-separated list of tags that apply to the current page.

The "Filter" header also accepts a comma-separated list of tags, but instead specifies which pages are included when
filtering the pages array. The plugin makes a Twig filter available, "apply_tag_filter", which you can use on the
pages array in your templates to be able to filter pages matching specific tags. On a page with no "Filter" header
this is a no-op, but on a page that specifies the header "Filter: foo, bar", `pages|apply_tag_filter` will return only
pages having at least one of those two tags.

## Breaking changes as of January 2020

If you were using an earlier version of the plugin, the current version will not work as before. Previously the `pages`
array was directly modified to apply the page's filter. This approach was not compatible with other Pico plugins and
themes which depend on the `pages` array to contain all pages.

Instead, this plugin now makes a Twig filter called `apply_tag_filter` available, which can be used in your templates
to filter the `pages` array on demand: `{% set filtered_pages = pages|apply_tag_filter %}`. This also makes it
possible to combine with other plugins that behave similarly:
`{% set results = pages|apply_tag_filter|apply_search|paginate %}`

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

Optionally, you can also use YAML sequences for `Tags`:
```
Tags:
  - blog
  - pancakes
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
{% for page in pages|apply_tag_filter if page.title %}
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
    {# List of all pages with a specified tag #}
    <ul>
    {% for page in pages if page.title and tags and not (page.id ends with 'index') %}
        {% if page.meta.tags is iterable %}
            {% set pageTags = page.meta.tags %}
        {% else %}
            {% set pageTags = page.meta.tags|split(',') %}
        {% endif %}
        
        {% for pageTag in pageTags %}
            {% if tag in pageTags %}
                <li>
                    <a href="{{ page.url }}">{{ page.title }}</a>
                </li>
            {% endif %}
        {% endfor %}
    {% endfor %}
    </ul>
{% else %}
    {# List of all tags used on the site #}
    <ul>
    {% for tag in tags|sort %}
        <li><a href="{{current_page.url}}/?tag={{ tag }}">{{ tag }}</li>
    {% endfor %}
    </ul>
{% endif %}
{% endblock content %}
```

This would make e.g. `https://www.example.com/tags?tag=pancakes` show a list of documents
with the tag `pancakes`, whereas `https://www.example.com/tags` would show a list of all available tags.
