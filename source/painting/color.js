/** Get the color for a given amplitude value
    @pre amplitude { 0 - 1 }, exponent{ >0 - <inf }
    @post fillStyle { "rgb(<r>,<g>,<b>,<a>)" }
 */ 
function colorFromAmplitude(amplitude, exponent = 1.0) {
    console.assert(colorPoints != null, "colorFromAmplitude, colorpoints array is never created!");
    
    const skewedAmplitude = Math.pow(amplitude, exponent);
    
    // Find index of the colors to interpolate with
    let baseColorIndex = colorPoints.length - 2;
    for(var i = 1; i < colorPoints.length; i++) {
        if(colorPoints[i][0] >= skewedAmplitude) {
            baseColorIndex = i - 1;
            break;
        }
    }
    console.assert(baseColorIndex < colorPoints.length - 1 && baseColorIndex >= 0, "colorFromAmplitude: baseColorIndex should larger then zero and smaller then the colorPoints array size: " + baseColorIndex);
    
    // Get linear interpolation amount
    const rangeBetweenColors = colorPoints[baseColorIndex + 1][0] - colorPoints[baseColorIndex][0];
    const rangeToValue = skewedAmplitude - colorPoints[baseColorIndex][0];
    const amount = rangeToValue / rangeBetweenColors;
    console.assert(rangeToValue >= 0.0, "colorFromAmplitude: index should be larger then 0");
    
    // Get interpolated color and created the CSS rgba text
    let fillStyle = "rgba(";
    for(var i = 0; i < 4; i++) {
        const brightness = amount * colorPoints[baseColorIndex + 1][1][i] + (1.0 - amount) * colorPoints[baseColorIndex][1][i];

        fillStyle += parseInt( brightness );

        if(i < 3) {
            fillStyle += ", ";
        }
    }
    fillStyle += ")";
    
    return fillStyle;
}
