from django import forms
from django.forms import BoundField
from django.forms.fields import BooleanField
from django.utils.html import escape
from django.utils.encoding import force_bytes
from django.utils.safestring import mark_safe


"""
https://www.djangosnippets.org/snippets/798/
"""


class SectionedForm(object):
    fieldsets = ()

    fieldset_template = "<div class='col-md-6 fieldset fieldset_%s'><div class='card border-light mb-3'>" \
                        "<div class='card-body'><h4 class='legend'>%s</h5><div class='fields_wrapper'>"
    fieldset_template_end = "</div></div></div></div>"

    def _html_output(self, normal_row, error_row, row_ender, help_text_html, errors_on_separate_row):
        "Helper function for outputting HTML. Used by as_table(), as_ul(), as_p()."
        top_errors = self.non_field_errors() # Errors that should be displayed above all fields.
        output, hidden_fields = [], []

        for fieldset_key, fieldset in self.Meta.fieldsets:
            output.append(self.fieldset_template % (fieldset_key, (fieldset['legend'] if 'legend' in fieldset
                                                                  else fieldset_key)))
            fields = fieldset['fields'] if fieldset else self.fields

            for name, field in [i for i in self.fields.items() if i[0] in fields]:

                bf = BoundField(self, field, name)
                bf_errors = self.error_class([escape(error) for error in bf.errors]) # Escape and cache in local variable.
                if bf.is_hidden:
                    if bf_errors:
                        top_errors.extend(['(Hidden field %s) %s' % (name, e) for e in bf_errors])
                    hidden_fields.append(bf)
                else:
                    bf.field.widget.attrs.update({'class': 'form-control'})
                    if isinstance(field, BooleanField):
                        bf.field.widget.attrs.update({'class': 'form-check-input'})
                    if errors_on_separate_row and bf_errors:
                        output.append(error_row % bf_errors)
                    if bf.label:
                        label = bf.label
                        # Only add the suffix if the label does not end in
                        # punctuation.
                        if field.required:
                            label += mark_safe('<span class="required">*</span>')
                        if self.label_suffix:
                            if label[-1] not in ':?.!':
                                label += self.label_suffix
                        label = bf.label_tag(mark_safe(label), {'class': "form-label"}) or ''
                    else:
                        label = ''
                    if field.help_text:
                        help_text = help_text_html % field.help_text
                    else:
                        help_text = ''
                    output.append(normal_row % {'html_class_attr': ''+name if bf_errors else name,
                                                'errors': error_row % bf_errors, 'label': label, 'field': bf,
                                                'help_text': help_text})

            if fieldset:
                output.append(self.fieldset_template_end)

        if top_errors:
            output.insert(0, '<div class="col-12"><div class="alert alert-danger global_error">' + error_row % top_errors + '</div></div>')
        if hidden_fields: # Insert any hidden fields in the last row.
            str_hidden = ''.join(hidden_fields)
            if output:
                last_row = output[-1]
                # Chop off the trailing row_ender (e.g. '</td></tr>') and
                # insert the hidden fields.
                output[-1] = last_row[:-len(row_ender)] + str_hidden + row_ender
            else:
                # If there aren't any rows in the output, just append the
                # hidden fields.
                output.append(str_hidden)
        return mark_safe('\n'.join(output))

    def as_bootstrap_div(self):
        """Return this form rendered as HTML <li>s -- excluding the <ul></ul>."""
        return self._html_output(
            normal_row='<div class="field_wrapper mb-3 %(html_class_attr)s">%(label)s %(field)s %(errors)s '
                       '%(help_text)s</div>',
            error_row='<div class="alert-danger">%s</div>',
            row_ender='</div>',
            help_text_html=' <div class="form-text">%s</div>',
            errors_on_separate_row=False,
        )