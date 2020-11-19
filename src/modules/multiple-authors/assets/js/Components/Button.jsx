class Button extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <input type="button"
                   className={"button " + this.props.className}
                   onClick={this.props.onClick}
                   disabled={!this.props.enabled}
                   value={this.props.label}/>
        );
    }
}

export default Button;
