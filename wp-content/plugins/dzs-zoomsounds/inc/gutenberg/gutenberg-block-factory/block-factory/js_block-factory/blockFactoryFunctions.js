/**
 *
 * @param configAttributes
 * @returns {*}
 */
export function sanitizeBlockAttributes(configAttributes){
  for(let configAttLabel in configAttributes){
    if(configAttributes[configAttLabel].choices){
      if(typeof configAttributes[configAttLabel].choices==='string' && configAttributes[configAttLabel].choices.indexOf('{{window')===0){
        const choices = /window--(.*?)}/g.exec(configAttributes[configAttLabel].choices);
        configAttributes[configAttLabel].choices = [
          {
            label: 'not found',
            value: 'not-found',
          }
        ]
        if(choices && choices[1] && window[choices[1]]){
          configAttributes[configAttLabel].choices = window[choices[1]];
        }
      }
    }
  }
  return configAttributes;
}