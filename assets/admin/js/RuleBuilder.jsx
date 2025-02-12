import { useEffect, useState } from '@wordpress/element';
import { Form } from '@rjsf/core';
import { sanitizeRules } from './RuleSanitizer';
import schema from './discountSchema.json';

export default function RuleBuilder({ initialData, onSave }) {
    const [formData, setFormData] = useState(initialData);
    const [errors, setErrors] = useState([]);

    const handleSubmit = ({ formData }) => {
        const { valid, errors } = validateForm(formData);
        if (valid) {
            onSave(sanitizeRules(formData));
        } else {
            setErrors(errors);
        }
    };

    return (
        <div className="cdwc-rule-builder">
            <Form
                schema={schema}
                formData={formData}
                onChange={({ formData }) => setFormData(formData)}
                onSubmit={handleSubmit}
                widgets={{
                    DateTimeWidget: ({ value, onChange }) => (
                        <input
                            type="datetime-local"
                            value={value}
                            onChange={(e) => onChange(e.target.value)}
                        />
                    )
                }}
            />
            {errors.length > 0 && (
                <div className="validation-errors">
                    {errors.map((err, i) => (
                        <div key={i}>{err}</div>
                    ))}
                </div>
            )}
        </div>
    );
}